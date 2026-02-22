<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/PurchaseOrder.php';

class PurchaseOrderController {

    public function create() {
        if (!Auth::check())                                   return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager']))       return Response::forbidden('Only Admin and Asset Manager can create purchase orders');
        if (!Request::isPost())                               return Response::methodNotAllowed('POST');

        $input = Request::json();

        $errors = [];
        if (empty($input['supplier']))                        $errors[] = 'Supplier is required';
        if (empty($input['items']) || !is_array($input['items'])) $errors[] = 'At least one item is required';
        if (!empty($errors))                                  return Response::error('Validation failed', 400, $errors);

        // Validate each item
        foreach ($input['items'] as $index => $item) {
            $n = $index + 1;
            if (empty($item['item_name']))                    $errors[] = "Item #{$n}: name is required";
            if (empty($item['item_type']) || !in_array($item['item_type'], ['stock','asset']))
                                                              $errors[] = "Item #{$n}: item_type must be stock or asset";
            if (!isset($item['quantity']) || $item['quantity'] <= 0)
                                                              $errors[] = "Item #{$n}: valid quantity is required";
            if (!isset($item['unit_price']) || $item['unit_price'] < 0)
                                                              $errors[] = "Item #{$n}: valid unit price is required";
            if ($item['item_type'] === 'stock' && empty($item['stock_id']))
                                                              $errors[] = "Item #{$n}: stock_id is required for stock items";
            if ($item['item_type'] === 'asset' && empty($item['asset_category']))
                                                              $errors[] = "Item #{$n}: asset_category is required for asset items";
        }
        if (!empty($errors))                                  return Response::error('Item validation failed', 400, $errors);

        $data = [
            'supplier'   => trim($input['supplier']),
            'notes'      => isset($input['notes'])      ? trim($input['notes'])      : null,
            'campus_id'  => $input['campus_id']         ?? Auth::campusId(),
            'created_by' => Auth::userId(),
        ];

        $items = [];
        foreach ($input['items'] as $item) {
            $items[] = [
                'item_type'      => $item['item_type'],
                'stock_id'       => $item['item_type'] === 'stock' ? intval($item['stock_id']) : null,
                'asset_category' => $item['item_type'] === 'asset' ? $item['asset_category']   : null,
                'asset_brand'    => $item['asset_brand']    ?? null,
                'asset_model'    => $item['asset_model']    ?? null,
                'item_name'      => trim($item['item_name']),
                'quantity'       => intval($item['quantity']),
                'unit_price'     => floatval($item['unit_price']),
                'notes'          => $item['notes'] ?? null,
            ];
        }

        $po = PurchaseOrder::create($data, $items);
        if ($po) return Response::success('Purchase order created successfully', $po, 201);
        return Response::serverError('Failed to create purchase order');
    }

    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $filters = [
            'status'          => Request::get('status'),
            'approval_status' => Request::get('approval_status'),
        ];

        $pos = Auth::isAdmin()
            ? PurchaseOrder::getAll($filters)
            : PurchaseOrder::getByCampus(Auth::campusId(), $filters);

        return Response::success('Purchase orders retrieved successfully', $pos, 200, ['count' => count($pos)]);
    }

    public function show() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $id = Request::get('id');
        if (!$id) return Response::error('Purchase order ID is required', 400);

        $po = PurchaseOrder::find($id);
        if (!$po) return Response::notFound('Purchase order not found');

        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to view this purchase order');

        return Response::success('Purchase order retrieved successfully', $po);
    }

    public function update() {
        if (!Auth::check())                             return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can update purchase orders');
        if (!Request::isPut() && !Request::isPatch())   return Response::methodNotAllowed('PUT, PATCH');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Purchase order ID is required', 400);

        $po_id = intval($input['id']);
        $po    = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to update this purchase order');

        if (in_array($po['status'], ['Completed', 'Cancelled']))
            return Response::error('Cannot update a ' . $po['status'] . ' purchase order', 400);

        $updateData = [];
        if (isset($input['supplier'])) $updateData['supplier'] = trim($input['supplier']);
        if (isset($input['notes']))    $updateData['notes']    = trim($input['notes']);
        if (isset($input['status'])) {
            $allowed = ['Draft', 'Pending Approval', 'Approved', 'Rejected', 'Completed', 'Cancelled'];
            if (!in_array($input['status'], $allowed)) return Response::error('Invalid status', 400);
            $updateData['status'] = $input['status'];
        }
        if (empty($updateData)) return Response::error('No fields to update', 400);

        $updated = PurchaseOrder::update($po_id, $updateData);
        if ($updated) return Response::success('Purchase order updated successfully', $updated);
        return Response::serverError('Failed to update purchase order');
    }

    public function submit() {
        if (!Auth::check())                             return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can submit purchase orders');
        if (!Request::isPost())                         return Response::methodNotAllowed('POST');

        $input  = Request::json();
        if (empty($input['id'])) return Response::error('Purchase order ID is required', 400);

        $po_id = intval($input['id']);
        $po    = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        if (!Auth::isAdmin() && ($po['campus_id'] != Auth::campusId() || $po['created_by'] != Auth::userId()))
            return Response::forbidden('You can only submit purchase orders you created');

        // Allow resubmit from Rejected too
        if (!in_array($po['status'], ['Draft', 'Rejected']))
            return Response::error('Can only submit Draft or Rejected purchase orders', 400);

        if ($po['items_count'] == 0)
            return Response::error('Cannot submit a purchase order with no items', 400);

        $submitted = PurchaseOrder::submit($po_id);
        if ($submitted) return Response::success('Purchase order submitted for approval', $submitted);
        return Response::serverError('Failed to submit purchase order');
    }

    public function approve() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Only Admin can approve or reject purchase orders');
        if (!Request::isPost() && !Request::isPatch()) return Response::methodNotAllowed('POST, PATCH');

        $input = Request::json();
        if (empty($input['id']))     return Response::error('Purchase order ID is required', 400);
        if (empty($input['action']) || !in_array($input['action'], ['approve','reject']))
            return Response::error('Action must be approve or reject', 400);

        $po_id  = intval($input['id']);
        $action = $input['action'];
        $reason = isset($input['reason']) ? trim($input['reason']) : null;

        $po = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        if ($po['status'] !== 'Pending Approval')
            return Response::error('Can only approve/reject POs with Pending Approval status', 400);

        if ($action === 'reject' && empty($reason))
            return Response::error('Rejection reason is required', 400);

        $result = PurchaseOrder::approveOrReject($po_id, $action, Auth::userId(), $reason);
        if ($result) {
            $msg = $action === 'approve' ? 'Purchase order approved' : 'Purchase order rejected';
            return Response::success($msg, $result);
        }
        return Response::serverError('Failed to update purchase order');
    }

    public function receive() {
        if (!Auth::check())                             return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can receive purchase orders');
        if (!Request::isPost())                         return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Purchase order ID is required', 400);

        $po_id = intval($input['id']);
        $po    = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to receive this purchase order');

        if ($po['status'] !== 'Approved')
            return Response::error('Can only receive Approved purchase orders', 400);

        $result = PurchaseOrder::receive($po_id, Auth::userId());
        if ($result) return Response::success('Purchase order received — stock updated and assets created', $result);
        return Response::serverError('Failed to receive purchase order');
    }

    public function cancel() {
        if (!Auth::check())                             return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can cancel purchase orders');
        if (!Request::isPost())                         return Response::methodNotAllowed('POST');

        $input  = Request::json();
        if (empty($input['id'])) return Response::error('Purchase order ID is required', 400);

        $po_id  = intval($input['id']);
        $reason = isset($input['reason']) ? trim($input['reason']) : 'No reason provided';

        $po = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        if (!Auth::isAdmin() && ($po['campus_id'] != Auth::campusId() || $po['created_by'] != Auth::userId()))
            return Response::forbidden('You can only cancel purchase orders you created');

        if (!in_array($po['status'], ['Draft', 'Pending Approval', 'Rejected']))
            return Response::error('Can only cancel Draft, Pending Approval or Rejected POs', 400);

        $cancelled = PurchaseOrder::cancel($po_id, $reason, Auth::userId());
        if ($cancelled) return Response::success('Purchase order cancelled', $cancelled);
        return Response::serverError('Failed to cancel purchase order');
    }

    public function delete() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admin can delete purchase orders');
        if (!Request::isDelete() && !Request::isPost()) return Response::methodNotAllowed('DELETE, POST');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Purchase order ID is required', 400);

        $po_id = intval($input['id']);
        $po    = PurchaseOrder::find($po_id);
        if (!$po) return Response::notFound('Purchase order not found');

        $deleted = PurchaseOrder::delete($po_id);
        if ($deleted) return Response::success('Purchase order deleted', ['po_id' => $po_id, 'po_number' => $po['po_number']]);
        return Response::serverError('Failed to delete purchase order');
    }
}
?>