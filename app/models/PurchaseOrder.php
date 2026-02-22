<?php
require_once __DIR__ . '/../../config/database.php';

class PurchaseOrder {

    /**
     * Create a new purchase order with items
     */
    public static function create($data, $items) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $db->beginTransaction();

            $po_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $total_amount = 0;
            foreach ($items as $item) {
                $total_amount += ($item['quantity'] * $item['unit_price']);
            }

            $stmt = $db->prepare(
                "INSERT INTO purchase_orders
                    (po_number, campus_id, supplier, total_amount, status, approval_status, created_by, notes, created_at)
                 VALUES
                    (:po_number, :campus_id, :supplier, :total_amount, 'Draft', 'Pending', :created_by, :notes, NOW())"
            );
            $stmt->execute([
                ':po_number'    => $po_number,
                ':campus_id'    => $data['campus_id'],
                ':supplier'     => $data['supplier'],
                ':total_amount' => $total_amount,
                ':created_by'   => $data['created_by'],
                ':notes'        => $data['notes'] ?? null,
            ]);

            $po_id = $db->lastInsertId();

            $item_stmt = $db->prepare(
                "INSERT INTO purchase_order_items
                    (po_id, item_type, stock_id, asset_category, asset_brand, asset_model,
                     item_name, quantity, unit_price, total_price, notes)
                 VALUES
                    (:po_id, :item_type, :stock_id, :asset_category, :asset_brand, :asset_model,
                     :item_name, :quantity, :unit_price, :total_price, :notes)"
            );

            foreach ($items as $item) {
                $total_price = $item['quantity'] * $item['unit_price'];
                $item_stmt->execute([
                    ':po_id'          => $po_id,
                    ':item_type'      => $item['item_type']      ?? 'stock',
                    ':stock_id'       => $item['stock_id']       ?? null,
                    ':asset_category' => $item['asset_category'] ?? null,
                    ':asset_brand'    => $item['asset_brand']    ?? null,
                    ':asset_model'    => $item['asset_model']    ?? null,
                    ':item_name'      => trim($item['item_name']),
                    ':quantity'       => intval($item['quantity']),
                    ':unit_price'     => floatval($item['unit_price']),
                    ':total_price'    => $total_price,
                    ':notes'          => $item['notes'] ?? null,
                ]);
            }

            $db->commit();
            return self::find($po_id);

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return false;
        }
    }

    /**
     * Find purchase order by ID with items
     */
    public static function find($id) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $stmt = $db->prepare(
                "SELECT po.*,
                        c.campus_name,
                        u.name  AS created_by_name,  u.email AS created_by_email,
                        a.name  AS approved_by_name, a.email AS approved_by_email
                 FROM purchase_orders po
                 LEFT JOIN campuses c ON po.campus_id  = c.id
                 LEFT JOIN users    u ON po.created_by = u.id
                 LEFT JOIN users    a ON po.approved_by = a.id
                 WHERE po.id = :id"
            );
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $po = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$po) return false;

            $po['items']       = self::getItems($id);
            $po['items_count'] = count($po['items']);

            return $po;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get PO items
     */
    public static function getItems($po_id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "SELECT poi.*, s.item_name AS stock_item_name
                 FROM purchase_order_items poi
                 LEFT JOIN stock s ON s.id = poi.stock_id
                 WHERE poi.po_id = :po_id
                 ORDER BY poi.id ASC"
            );
            $stmt->bindParam(':po_id', $po_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all purchase orders (Admin view)
     */
    public static function getAll($filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT po.*, c.campus_name, u.name AS created_by_name, a.name AS approved_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id   = c.id
                      LEFT JOIN users    u ON po.created_by  = u.id
                      LEFT JOIN users    a ON po.approved_by = a.id
                      WHERE 1=1";

            $params = [];
            if (!empty($filters['status'])) {
                $query .= " AND po.status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['approval_status'])) {
                $query .= " AND po.approval_status = :approval_status";
                $params[':approval_status'] = $filters['approval_status'];
            }

            $query .= " ORDER BY po.created_at DESC";
            $stmt   = $db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get purchase orders by campus
     */
    public static function getByCampus($campus_id, $filters = []) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT po.*, c.campus_name, u.name AS created_by_name, a.name AS approved_by_name
                      FROM purchase_orders po
                      LEFT JOIN campuses c ON po.campus_id   = c.id
                      LEFT JOIN users    u ON po.created_by  = u.id
                      LEFT JOIN users    a ON po.approved_by = a.id
                      WHERE po.campus_id = :campus_id";

            $params = [':campus_id' => $campus_id];
            if (!empty($filters['status'])) {
                $query .= " AND po.status = :status";
                $params[':status'] = $filters['status'];
            }

            $query .= " ORDER BY po.created_at DESC";
            $stmt   = $db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Update purchase order header fields
     */
    public static function update($id, $data) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                $fields[]          = "$key = :$key";
                $params[":$key"]   = $value;
            }
            $fields[] = "updated_at = NOW()";

            $stmt = $db->prepare("UPDATE purchase_orders SET " . implode(', ', $fields) . " WHERE id = :id");
            if ($stmt->execute($params)) return self::find($id);
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Submit PO for approval
     */
    public static function submit($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "UPDATE purchase_orders
                 SET status = 'Pending Approval', approval_status = 'Pending', updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) return self::find($id);
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Approve or Reject PO — rejection_reason stored on reject
     */
    public static function approveOrReject($id, $action, $approved_by, $reason = null) {
        try {
            $db = (new Database())->getConnection();

            if ($action === 'approve') {
                $stmt = $db->prepare(
                    "UPDATE purchase_orders
                     SET approval_status = 'Approved', status = 'Approved',
                         approved_by = :approved_by, approved_at = NOW(),
                         rejection_reason = NULL, updated_at = NOW()
                     WHERE id = :id"
                );
                $stmt->execute([':approved_by' => $approved_by, ':id' => $id]);
            } else {
                $stmt = $db->prepare(
                    "UPDATE purchase_orders
                     SET approval_status = 'Rejected', status = 'Rejected',
                         approved_by = :approved_by, approved_at = NOW(),
                         rejection_reason = :reason, updated_at = NOW()
                     WHERE id = :id"
                );
                $stmt->execute([':approved_by' => $approved_by, ':reason' => $reason, ':id' => $id]);
            }

            return self::find($id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Receive PO — updates stock OR creates assets per item
     */
    public static function receive($id, $received_by) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $db->beginTransaction();

            $po = self::find($id);
            if (!$po) { $db->rollBack(); return false; }

            $stock_updates  = [];
            $assets_created = [];

            foreach ($po['items'] as $item) {

                if ($item['item_type'] === 'stock') {
                    // ── STOCK: increase quantity ──
                    if ($item['stock_id']) {
                        $upd = $db->prepare(
                            "UPDATE stock SET quantity = quantity + :qty, last_updated = NOW() WHERE id = :id"
                        );
                        $upd->execute([':qty' => $item['quantity'], ':id' => $item['stock_id']]);
                        $stock_updates[] = [
                            'item_name' => $item['item_name'],
                            'qty_added' => $item['quantity'],
                            'stock_id'  => $item['stock_id'],
                        ];
                    } else {
                        // No stock_id — try to match by name, else create new
                        $find = $db->prepare(
                            "SELECT id FROM stock WHERE campus_id = :cid AND LOWER(item_name) = LOWER(:name) LIMIT 1"
                        );
                        $find->execute([':cid' => $po['campus_id'], ':name' => $item['item_name']]);
                        $existing = $find->fetch(PDO::FETCH_ASSOC);

                        if ($existing) {
                            $upd = $db->prepare(
                                "UPDATE stock SET quantity = quantity + :qty, last_updated = NOW() WHERE id = :id"
                            );
                            $upd->execute([':qty' => $item['quantity'], ':id' => $existing['id']]);
                        } else {
                            $ins = $db->prepare(
                                "INSERT INTO stock (campus_id, item_name, category, quantity, unit, minimum_threshold, created_at)
                                 VALUES (:cid, :name, 'Other', :qty, 'units', 10, NOW())"
                            );
                            $ins->execute([
                                ':cid'  => $po['campus_id'],
                                ':name' => $item['item_name'],
                                ':qty'  => $item['quantity'],
                            ]);
                        }
                        $stock_updates[] = ['item_name' => $item['item_name'], 'qty_added' => $item['quantity']];
                    }

                } elseif ($item['item_type'] === 'asset') {
                    // ── ASSET: create one row per unit ──
                    $category = $item['asset_category'] ?? 'Other';
                    $prefix_map = [
                        'Laptop'            => 'L',
                        'Printer'           => 'P',
                        'Network Equipment' => 'N',
                        'Furniture'         => 'F',
                        'Other'             => 'O',
                    ];
                    $prefix = $prefix_map[$category] ?? 'O';

                    for ($i = 0; $i < intval($item['quantity']); $i++) {
                        $asset_tag = 'AST-' . $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                        $asset_name = trim(implode(' ', array_filter([
                            $item['asset_brand'],
                            $item['asset_model'] ?: $item['item_name'],
                        ])));

                        $ins = $db->prepare(
                            "INSERT INTO assets
                                (asset_tag, campus_id, category, name, description, status,
                                 po_item_id, created_at, updated_at)
                             VALUES
                                (:tag, :cid, :category, :name, :desc, 'Available',
                                 :po_item_id, NOW(), NOW())"
                        );
                        $ins->execute([
                            ':tag'        => $asset_tag,
                            ':cid'        => $po['campus_id'],
                            ':category'   => $category,
                            ':name'       => $asset_name ?: $item['item_name'],
                            ':desc'       => 'Received via PO ' . $po['po_number'],
                            ':po_item_id' => $item['id'],
                        ]);

                        $assets_created[] = ['asset_tag' => $asset_tag, 'name' => $asset_name];
                    }
                }
            }

            // Mark PO as Completed
            $complete = $db->prepare(
                "UPDATE purchase_orders
                 SET status = 'Completed', updated_at = NOW()
                 WHERE id = :id"
            );
            $complete->execute([':id' => $id]);

            $db->commit();

            $result = self::find($id);
            $result['stock_updates']  = $stock_updates;
            $result['assets_created'] = $assets_created;
            return $result;

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            return false;
        }
    }

    /**
     * Cancel purchase order
     */
    public static function cancel($id, $reason, $cancelled_by) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "UPDATE purchase_orders
                 SET status = 'Cancelled', approval_status = 'Cancelled',
                     rejection_reason = :reason, updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([':reason' => $reason, ':id' => $id]);
            return self::find($id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete (soft) purchase order
     */
    public static function delete($id) {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "UPDATE purchase_orders
                 SET status = 'Cancelled', approval_status = 'Cancelled', updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get pending approval POs (for dashboard)
     */
    public static function getPendingApproval() {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "SELECT po.*, u.name AS created_by_name
                 FROM purchase_orders po
                 LEFT JOIN users u ON u.id = po.created_by
                 WHERE po.status = 'Pending Approval'
                 ORDER BY po.created_at ASC
                 LIMIT 5"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get open PO count (for dashboard)
     */
    public static function getOpenCount() {
        try {
            $db   = (new Database())->getConnection();
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM purchase_orders WHERE status NOT IN ('Completed','Cancelled')"
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>