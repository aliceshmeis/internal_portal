<?php
require_once __DIR__ . '/../../config/database.php';

class Quotation {

    // ─── CREATE ───────────────────────────────────────────────────────────────
    public static function create($data) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                INSERT INTO quotations
                    (quotation_number, supplier_id, campus_id, total_amount,
                     quotation_date, valid_until, status, file_path, notes, created_by)
                VALUES
                    (:quotation_number, :supplier_id, :campus_id, :total_amount,
                     :quotation_date, :valid_until, 'Pending', :file_path, :notes, :created_by)
            ");
            $stmt->execute([
                ':quotation_number' => trim($data['quotation_number']),
                ':supplier_id'      => intval($data['supplier_id']),
                ':campus_id'        => intval($data['campus_id']),
                ':total_amount'     => floatval($data['total_amount']),
                ':quotation_date'   => $data['quotation_date'],
                ':valid_until'      => $data['valid_until'],
                ':file_path'        => $data['file_path']  ?? null,
                ':notes'            => $data['notes']      ?? null,
                ':created_by'       => intval($data['created_by']),
            ]);
            $id = $db->lastInsertId();
            self::checkExpiry($id);
            return self::find($id);
        } catch (Exception $e) { return false; }
    }

    // ─── FIND ─────────────────────────────────────────────────────────────────
    public static function find($id) {
        try {
            $db = (new Database())->getConnection();
            self::checkExpiry($id);
            $stmt = $db->prepare("
                SELECT q.*,
                       s.name    AS supplier_name,  s.email   AS supplier_email,
                       s.phone   AS supplier_phone, s.address AS supplier_address,
                       c.campus_name,
                       u.name    AS created_by_name,
                       a.name    AS approved_by_name
                FROM quotations q
                LEFT JOIN suppliers s ON q.supplier_id = s.id
                LEFT JOIN campuses  c ON q.campus_id   = c.id
                LEFT JOIN users     u ON q.created_by  = u.id
                LEFT JOIN users     a ON q.approved_by = a.id
                WHERE q.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $q = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$q) return false;
            $q['requests'] = self::getRequests($id);
            return $q;
        } catch (Exception $e) { return false; }
    }

    // ─── LIST BY CAMPUS ───────────────────────────────────────────────────────
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $db = (new Database())->getConnection();

            $db->prepare("
                UPDATE quotations
                SET status = 'Expired'
                WHERE campus_id = :cid
                  AND status NOT IN ('Approved','Rejected','Expired')
                  AND valid_until < CURDATE()
            ")->execute([':cid' => $campus_id]);

            $query  = "
                SELECT q.*,
                       s.name AS supplier_name,
                       c.campus_name,
                       u.name AS created_by_name
                FROM quotations q
                LEFT JOIN suppliers s ON q.supplier_id = s.id
                LEFT JOIN campuses  c ON q.campus_id   = c.id
                LEFT JOIN users     u ON q.created_by  = u.id
                WHERE q.campus_id = :campus_id
            ";
            $params = [':campus_id' => $campus_id];

            if (!empty($filters['status'])) {
                $query .= " AND q.status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['supplier_id'])) {
                $query .= " AND q.supplier_id = :supplier_id";
                $params[':supplier_id'] = $filters['supplier_id'];
            }

            $query .= " ORDER BY q.created_at DESC";
            $stmt   = $db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ─── APPROVE ──────────────────────────────────────────────────────────────
    public static function approve($id, $approved_by) {
        try {
            $db = (new Database())->getConnection();
            $q  = self::findRaw($id);
            if (!$q)                               return ['ok' => false, 'msg' => 'Quotation not found'];
            if ($q['valid_until'] < date('Y-m-d')) return ['ok' => false, 'msg' => 'Cannot approve an expired quotation'];
            if ($q['status'] !== 'Pending')        return ['ok' => false, 'msg' => 'Only Pending quotations can be approved'];

            $db->prepare("
                UPDATE quotations
                SET status = 'Approved', approved_by = :approved_by, approved_at = NOW(), updated_at = NOW()
                WHERE id = :id
            ")->execute([':approved_by' => $approved_by, ':id' => $id]);

            self::auditLog($approved_by, $q['campus_id'], 'approve_quotation', 'quotations', $id,
    ['status' => 'Pending'], ['status' => 'Approved']);

// Auto-generate PO immediately after approval
$po = self::generatePO($id, $approved_by);

return ['ok' => true, 'data' => self::find($id), 'po' => $po];
        } catch (Exception $e) { return ['ok' => false, 'msg' => 'DB error']; }
    }

    // ─── REJECT ───────────────────────────────────────────────────────────────
    public static function reject($id, $approved_by, $reason) {
        try {
            $db = (new Database())->getConnection();
            $q  = self::findRaw($id);
            if (!$q)                        return ['ok' => false, 'msg' => 'Quotation not found'];
            if ($q['status'] !== 'Pending') return ['ok' => false, 'msg' => 'Only Pending quotations can be rejected'];

            $db->prepare("
                UPDATE quotations
                SET status = 'Rejected', approved_by = :approved_by,
                    approved_at = NOW(), rejection_reason = :reason, updated_at = NOW()
                WHERE id = :id
            ")->execute([':approved_by' => $approved_by, ':reason' => $reason, ':id' => $id]);

            self::auditLog($approved_by, $q['campus_id'], 'reject_quotation', 'quotations', $id,
                ['status' => 'Pending'], ['status' => 'Rejected', 'reason' => $reason]);

            return ['ok' => true, 'data' => self::find($id)];
        } catch (Exception $e) { return ['ok' => false, 'msg' => 'DB error']; }
    }

    // ─── GENERATE PO ──────────────────────────────────────────────────────────
    public static function generatePO($id, $created_by) {
        try {
            $db = (new Database())->getConnection();
            $q  = self::findRaw($id);

            if (!$q)                         return ['ok' => false, 'msg' => 'Quotation not found'];
            if ($q['status'] !== 'Approved') return ['ok' => false, 'msg' => 'Only Approved quotations can generate a PO'];

            $existing = $db->prepare("SELECT id FROM purchase_orders WHERE quotation_id = :qid LIMIT 1");
            $existing->execute([':qid' => $id]);
            if ($existing->fetch()) return ['ok' => false, 'msg' => 'A PO already exists for this quotation'];

            $db->beginTransaction();

            $po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $sup = $db->prepare("SELECT name FROM suppliers WHERE id = :id");
            $sup->execute([':id' => $q['supplier_id']]);
            $supplier_name = $sup->fetchColumn();

            $db->prepare("
                INSERT INTO purchase_orders
                    (quotation_id, po_number, campus_id, supplier, total_amount,
                     status, approval_status, created_by, notes, created_at)
                VALUES
                    (:quotation_id, :po_number, :campus_id, :supplier, :total_amount,
                     'Approved', 'Approved', :created_by, :notes, NOW())
            ")->execute([
                ':quotation_id' => $id,
                ':po_number'    => $po_number,
                ':campus_id'    => $q['campus_id'],
                ':supplier'     => $supplier_name,
                ':total_amount' => $q['total_amount'],
                ':created_by'   => $created_by,
                ':notes'        => 'Generated from Quotation #' . $q['quotation_number'],
            ]);

            $po_id = $db->lastInsertId();
            $db->commit();

            self::auditLog($created_by, $q['campus_id'], 'generate_po', 'purchase_orders', $po_id,
                [], ['po_number' => $po_number, 'quotation_id' => $id]);

            return ['ok' => true, 'po_id' => $po_id, 'po_number' => $po_number];
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return ['ok' => false, 'msg' => 'Failed to generate PO'];
        }
    }

    // ─── SEND QUOTATION REQUEST EMAILS ────────────────────────────────────────
    public static function sendRequests($data) {
       require_once __DIR__ . '/../helpers/Mailer.php';

        $db           = (new Database())->getConnection();
        $supplier_ids = $data['supplier_ids'];
        $due_date     = $data['response_due_date'] ?? null;
        $notes        = $data['notes'] ?? '';
        $campus_id    = $data['campus_id'];
        $requested_by = $data['requested_by'];
        $quotation_id = $data['quotation_id'] ?? null;

        $results = [];

        foreach ($supplier_ids as $supplier_id) {
            $sup = $db->prepare("SELECT * FROM suppliers WHERE id = :id");
            $sup->execute([':id' => $supplier_id]);
            $supplier = $sup->fetch(PDO::FETCH_ASSOC);

            if (!$supplier || empty($supplier['email'])) {
                $results[] = ['supplier_id' => $supplier_id, 'status' => 'Failed', 'error' => 'No email address'];
                continue;
            }

            $subject = 'Request for Quotation — Lebanese International University';
            $due_str = $due_date ? date('d M Y', strtotime($due_date)) : 'As soon as possible';

            $htmlBody = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                    <div style='background:#1e40af;padding:24px;border-radius:8px 8px 0 0;'>
                        <h2 style='color:#fff;margin:0;'>Request for Quotation</h2>
                        <p style='color:#bfdbfe;margin:4px 0 0;'>Lebanese International University — Procurement</p>
                    </div>
                    <div style='background:#f8fafc;padding:24px;border:1px solid #e2e8f0;border-radius:0 0 8px 8px;'>
                        <p>Dear <strong>" . htmlspecialchars($supplier['name']) . "</strong>,</p>
                        <p>We would like to request a formal quotation from your company for the items/services required by our institution.</p>
                        " . ($notes ? "<p><strong>Details:</strong><br>" . nl2br(htmlspecialchars($notes)) . "</p>" : "") . "
                        <div style='background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin:16px 0;'>
                            <p style='margin:0;'><strong>Response Required By:</strong> {$due_str}</p>
                        </div>
                        <p>Please reply to this email with your quotation PDF and pricing details.</p>
                        <p style='color:#64748b;font-size:13px;margin-top:24px;'>
                            This request was sent by the Procurement Office.<br>
                            Lebanese International University
                        </p>
                    </div>
                </div>
            ";

            $sent   = Mailer::send($supplier['email'], $supplier['name'], $subject, $htmlBody);
            $status = $sent['success'] ? 'Sent' : 'Failed';
            $error  = $sent['success'] ? null : ($sent['error'] ?? 'Unknown error');

            $db->prepare("
                INSERT INTO quotation_requests
                    (quotation_id, supplier_id, campus_id, requested_by,
                     response_due_date, status, email_subject, email_body, error_message, notes)
                VALUES
                    (:quotation_id, :supplier_id, :campus_id, :requested_by,
                     :due_date, :status, :subject, :body, :error, :notes)
            ")->execute([
                ':quotation_id' => $quotation_id,
                ':supplier_id'  => $supplier_id,
                ':campus_id'    => $campus_id,
                ':requested_by' => $requested_by,
                ':due_date'     => $due_date,
                ':status'       => $status,
                ':subject'      => $subject,
                ':body'         => $htmlBody,
                ':error'        => $error,
                ':notes'        => $notes,
            ]);

            $results[] = [
                'supplier_id'    => $supplier_id,
                'supplier_name'  => $supplier['name'],
                'supplier_email' => $supplier['email'],
                'status'         => $status,
                'error'          => $error,
            ];
        }

        return $results;
    }

    // ─── RESEND ───────────────────────────────────────────────────────────────
    public static function resend($request_id, $requested_by) {
        require_once __DIR__ . '/../helpers/Mailer.php';

        $db   = (new Database())->getConnection();
        $stmt = $db->prepare("
            SELECT qr.*, s.name AS supplier_name, s.email AS supplier_email
            FROM quotation_requests qr
            JOIN suppliers s ON qr.supplier_id = s.id
            WHERE qr.id = :id
        ");
        $stmt->execute([':id' => $request_id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$req) return ['ok' => false, 'msg' => 'Request not found'];

        $sent   = Mailer::send($req['supplier_email'], $req['supplier_name'], $req['email_subject'], $req['email_body']);
        $status = $sent['success'] ? 'Sent' : 'Failed';

        $db->prepare("
            UPDATE quotation_requests
            SET status = :status, error_message = :error, requested_at = NOW()
            WHERE id = :id
        ")->execute([
            ':status' => $status,
            ':error'  => $sent['success'] ? null : ($sent['error'] ?? ''),
            ':id'     => $request_id,
        ]);

        return ['ok' => $sent['success'], 'status' => $status];
    }

    // ─── MARK REQUEST RECEIVED ────────────────────────────────────────────────
    public static function markReceived($request_id) {
        try {
            $db = (new Database())->getConnection();
            $db->prepare("UPDATE quotation_requests SET status = 'Received' WHERE id = :id")
               ->execute([':id' => $request_id]);
            return true;
        } catch (Exception $e) { return false; }
    }

    // ─── UPLOAD SUPPLIER REPLY PDF ────────────────────────────────────────────
    // PDF only · stored in public/uploads/quotations/ · saves file_path + uploaded_at
    public static function uploadFile($id, $file) {

        // 1. Extension check — PDF only
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf')
            return ['ok' => false, 'msg' => 'Only PDF files are allowed. Please upload a .pdf file.'];

        // 2. MIME type check — double validation
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mimeType !== 'application/pdf')
            return ['ok' => false, 'msg' => 'The file does not appear to be a valid PDF.'];

        // 3. Size check — max 10MB
        if ($file['size'] > 10 * 1024 * 1024)
            return ['ok' => false, 'msg' => 'File too large. Maximum allowed size is 10MB.'];

        // 4. Ensure upload directory exists
        $upload_dir = __DIR__ . '/../../public/uploads/quotations/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true))
                return ['ok' => false, 'msg' => 'Could not create upload directory. Check server permissions.'];
        }

        // 5. Generate safe unique filename — no user input in filename
        $filename = 'QUO-' . intval($id) . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.pdf';
        $dest     = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest))
            return ['ok' => false, 'msg' => 'Failed to save file. Check folder write permissions.'];

        // 6. Save web path + uploaded_at in DB
        $web_path = '/internal_portal/public/uploads/quotations/' . $filename;
        try {
            $db = (new Database())->getConnection();
            $db->prepare("
                UPDATE quotations
                SET file_path   = :path,
                    uploaded_at = NOW(),
                    updated_at  = NOW()
                WHERE id = :id
            ")->execute([':path' => $web_path, ':id' => intval($id)]);

            return [
                'ok'        => true,
                'file_path' => $web_path,
                'filename'  => $filename,
            ];
        } catch (Exception $e) {
            return ['ok' => false, 'msg' => 'File saved to disk but database update failed.'];
        }
    }

    // ─── GET REQUESTS FOR A QUOTATION ─────────────────────────────────────────
    public static function getRequests($quotation_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("
                SELECT qr.*, s.name AS supplier_name, s.email AS supplier_email,
                       u.name AS requested_by_name
                FROM quotation_requests qr
                JOIN suppliers s ON qr.supplier_id  = s.id
                JOIN users     u ON qr.requested_by = u.id
                WHERE qr.quotation_id = :id
                ORDER BY qr.requested_at DESC
            ");
            $stmt->execute([':id' => $quotation_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ─── PRIVATE HELPERS ──────────────────────────────────────────────────────

    private static function findRaw($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT * FROM quotations WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return false; }
    }

    private static function checkExpiry($id) {
        try {
            $db = (new Database())->getConnection();
            $db->prepare("
                UPDATE quotations SET status = 'Expired', updated_at = NOW()
                WHERE id = :id AND status = 'Pending' AND valid_until < CURDATE()
            ")->execute([':id' => $id]);
        } catch (Exception $e) {}
    }

    private static function auditLog($user_id, $campus_id, $action, $table, $record_id, $old, $new) {
        try {
            $db = (new Database())->getConnection();
            $db->prepare("
                INSERT INTO audit_logs
                    (user_id, campus_id, action, table_name, record_id, old_values, new_values, created_at)
                VALUES
                    (:user_id, :campus_id, :action, :table, :record_id, :old, :new, NOW())
            ")->execute([
                ':user_id'   => $user_id,
                ':campus_id' => $campus_id,
                ':action'    => $action,
                ':table'     => $table,
                ':record_id' => $record_id,
                ':old'       => json_encode($old),
                ':new'       => json_encode($new),
            ]);
        } catch (Exception $e) {}
    }
}
?>