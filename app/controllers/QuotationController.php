<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Quotation.php';
require_once __DIR__ . '/../Models/Supplier.php';

class QuotationController {

    // GET api/v1/quotations/list.php
    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Admins only');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $filters = [
            'status'      => Request::get('status')      ?? null,
            'supplier_id' => Request::get('supplier_id') ?? null,
        ];

        $quotations = Quotation::getByCampus(Auth::campusId(), $filters);
        return Response::success('Quotations retrieved', $quotations, 200, ['count' => count($quotations)]);
    }

    // GET api/v1/quotations/show.php?id=X
    public function show() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Admins only');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $id = intval(Request::get('id'));
        if (!$id) return Response::error('id is required', 400);

        $q = Quotation::find($id);
        if (!$q) return Response::notFound('Quotation not found');

        if ($q['campus_id'] != Auth::campusId())
            return Response::forbidden('Access denied');

        return Response::success('Quotation retrieved', $q);
    }

    // POST api/v1/quotations/create.php
    public function create() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

        if (empty($input['quotation_number'])) return Response::error('Quotation number is required', 400);
        if (empty($input['supplier_id']))       return Response::error('Supplier is required', 400);
        if (empty($input['total_amount']))      return Response::error('Total amount is required', 400);
        if (empty($input['quotation_date']))    return Response::error('Quotation date is required', 400);
        if (empty($input['valid_until']))       return Response::error('Valid until date is required', 400);

        // Block if already expired on creation
        if ($input['valid_until'] < date('Y-m-d'))
            return Response::error('Valid until date cannot be in the past', 400);

        $supplier = Supplier::find(intval($input['supplier_id']));
        if (!$supplier) return Response::notFound('Supplier not found');

        $q = Quotation::create([
            'quotation_number' => trim($input['quotation_number']),
            'supplier_id'      => intval($input['supplier_id']),
            'campus_id'        => Auth::campusId(),
            'total_amount'     => floatval($input['total_amount']),
            'quotation_date'   => $input['quotation_date'],
            'valid_until'      => $input['valid_until'],
            'notes'            => $input['notes'] ?? null,
            'file_path'        => null,
            'created_by'       => Auth::userId(),
        ]);

        if ($q) return Response::success('Quotation created', $q, 201);
        return Response::serverError('Failed to create quotation');
    }

    // POST api/v1/quotations/approve.php
    public function approve() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input  = Request::json();
        $id     = intval($input['id']     ?? 0);
        $action = $input['action']        ?? '';
        $reason = trim($input['reason']   ?? '');

        if (!$id)                                      return Response::error('id is required', 400);
        if (!in_array($action, ['approve', 'reject'])) return Response::error('action must be approve or reject', 400);
        if ($action === 'reject' && empty($reason))    return Response::error('Rejection reason is required', 400);

        if ($action === 'approve') {
            $result = Quotation::approve($id, Auth::userId());
        } else {
            $result = Quotation::reject($id, Auth::userId(), $reason);
        }

        if ($result['ok']) {
            $msg = $action === 'approve' ? 'Quotation approved' : 'Quotation rejected';
            return Response::success($msg, $result['data']);
        }
        return Response::error($result['msg'], 400);
    }

    // POST api/v1/quotations/generate-po.php
    public function generatePO() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        $id    = intval($input['id'] ?? 0);
        if (!$id) return Response::error('id is required', 400);

        $result = Quotation::generatePO($id, Auth::userId());

        if ($result['ok']) return Response::success('PO generated successfully', $result, 201);
        return Response::error($result['msg'], 400);
    }

    // POST api/v1/quotations/send-request.php
    public function sendRequest() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

        if (empty($input['supplier_ids']) || !is_array($input['supplier_ids']))
            return Response::error('At least one supplier must be selected', 400);

        $results = Quotation::sendRequests([
            'supplier_ids'      => array_map('intval', $input['supplier_ids']),
            'response_due_date' => $input['response_due_date'] ?? null,
            'notes'             => $input['notes']             ?? '',
            'campus_id'         => Auth::campusId(),
            'requested_by'      => Auth::userId(),
            'quotation_id'      => !empty($input['quotation_id']) ? intval($input['quotation_id']) : null,
        ]);

        $sent   = count(array_filter($results, fn($r) => $r['status'] === 'Sent'));
        $failed = count($results) - $sent;

        return Response::success("Sent: {$sent}, Failed: {$failed}", $results);
    }

    // POST api/v1/quotations/resend.php
    public function resend() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input      = Request::json();
        $request_id = intval($input['request_id'] ?? 0);
        if (!$request_id) return Response::error('request_id is required', 400);

        $result = Quotation::resend($request_id, Auth::userId());
        if ($result['ok']) return Response::success('Email resent', $result);
        return Response::error($result['msg'] ?? 'Failed to resend', 400);
    }

    // POST api/v1/quotations/mark-received.php
    public function markReceived() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input      = Request::json();
        $request_id = intval($input['request_id'] ?? 0);
        if (!$request_id) return Response::error('request_id is required', 400);

        $ok = Quotation::markReceived($request_id);
        if ($ok) return Response::success('Marked as received', null);
        return Response::serverError('Failed');
    }

    // POST api/v1/quotations/upload.php  (multipart/form-data)
    public function upload() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $id = intval($_POST['quotation_id'] ?? 0);
        if (!$id) return Response::error('quotation_id is required', 400);
        if (empty($_FILES['file'])) return Response::error('No file uploaded', 400);

        $result = Quotation::uploadFile($id, $_FILES['file']);
        if ($result['ok']) return Response::success('File uploaded', $result);
        return Response::error($result['msg'], 400);
    }
}
?>