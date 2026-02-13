<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/PurchaseOrder.php';

class PurchaseOrderController {
    
    /**
     * Create a new purchase order
     */
    public function create() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can create purchase orders');
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        $errors = [];
        if (empty($input['supplier'])) {
            $errors[] = 'Supplier is required';
        }
        if (empty($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            $errors[] = 'At least one item is required';
        }
        
        if (!empty($errors)) {
            return Response::error('Validation failed', 400, $errors);
        }
        
        // Validate items
        foreach ($input['items'] as $index => $item) {
            if (empty($item['item_name'])) {
                $errors[] = "Item #" . ($index + 1) . ": Item name is required";
            }
            if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                $errors[] = "Item #" . ($index + 1) . ": Valid quantity is required";
            }
            if (!isset($item['unit_price']) || !is_numeric($item['unit_price']) || $item['unit_price'] < 0) {
                $errors[] = "Item #" . ($index + 1) . ": Valid unit price is required";
            }
        }
        
        if (!empty($errors)) {
            return Response::error('Item validation failed', 400, $errors);
        }
        
        // Prepare data
        $data = [
            'supplier' => trim($input['supplier']),
            'notes' => isset($input['notes']) ? trim($input['notes']) : null,
            'campus_id' => Auth::campusId(),
            'created_by' => Auth::userId()
        ];
        
        // Prepare items
        $items = [];
        foreach ($input['items'] as $item) {
            $items[] = [
                'item_name' => trim($item['item_name']),
                'quantity' => intval($item['quantity']),
                'unit_price' => floatval($item['unit_price']),
                'notes' => isset($item['notes']) ? trim($item['notes']) : null
            ];
        }
        
        // Create PO
        $po = PurchaseOrder::create($data, $items);
        
        if ($po) {
            return Response::success('Purchase order created successfully', $po, 201);
        } else {
            return Response::serverError('Failed to create purchase order');
        }
    }
    
    /**
     * List all purchase orders
     */
    public function list() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        // Get filters
        $filters = [
            'status' => Request::get('status'),
            'approval_status' => Request::get('approval_status')
        ];
        
        // Get POs based on role
        if (Auth::isAdmin()) {
            $pos = PurchaseOrder::getAll($filters);
        } else {
            $pos = PurchaseOrder::getByCampus(Auth::campusId(), $filters);
        }
        
        return Response::success('Purchase orders retrieved successfully', $pos, 200, [
            'count' => count($pos)
        ]);
    }
    
    /**
     * Show single purchase order
     */
    public function show() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        // Get ID
        $id = Request::get('id');
        if (!$id) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        // Get PO
        $po = PurchaseOrder::find($id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view this purchase order');
        }
        
        return Response::success('Purchase order retrieved successfully', $po);
    }
    
    /**
     * Update purchase order
     */
    public function update() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can update purchase orders');
        }
        
        // Method check
        if (!Request::isPut() && !Request::isPatch()) {
            return Response::methodNotAllowed('PUT, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        $po_id = intval($input['id']);
        
        // Get existing PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this purchase order');
        }
        
        // Can't update if completed or cancelled
        if (in_array($po['status'], ['Completed', 'Cancelled'])) {
            return Response::error('Cannot update purchase order with status: ' . $po['status'], 400);
        }
        
        // Build update data
        $updateData = [];
        
        if (isset($input['supplier'])) {
            $updateData['supplier'] = trim($input['supplier']);
        }
        
        if (isset($input['status'])) {
            $allowed = ['Draft', 'Pending Approval', 'Approved', 'Rejected', 'Completed', 'Cancelled'];
            if (!in_array($input['status'], $allowed)) {
                return Response::error('Invalid status', 400);
            }
            $updateData['status'] = $input['status'];
        }
        
        if (isset($input['notes'])) {
            $updateData['notes'] = trim($input['notes']);
        }
        
        if (empty($updateData)) {
            return Response::error('No fields to update', 400);
        }
        
        // Update PO
        $updated = PurchaseOrder::update($po_id, $updateData);
        
        if ($updated) {
            return Response::success('Purchase order updated successfully', $updated);
        } else {
            return Response::serverError('Failed to update purchase order');
        }
    }
    
    /**
     * Submit PO for approval
     */
    public function submit() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can submit purchase orders');
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        $po_id = intval($input['id']);
        
        // Get PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Permission check - Asset Manager can only submit their own
        if (!Auth::isAdmin() && ($po['campus_id'] != Auth::campusId() || $po['created_by'] != Auth::userId())) {
            return Response::forbidden('You can only submit purchase orders you created');
        }
        
        // Can only submit if Draft
        if ($po['status'] !== 'Draft') {
            return Response::error('Can only submit purchase orders with Draft status', 400);
        }
        
        // Check if has items
        if ($po['items_count'] == 0) {
            return Response::error('Cannot submit purchase order without items', 400);
        }
        
        // Submit PO
        $submitted = PurchaseOrder::submit($po_id);
        
        if ($submitted) {
            return Response::success('Purchase order submitted for approval successfully', $submitted);
        } else {
            return Response::serverError('Failed to submit purchase order');
        }
    }
    
    /**
     * Approve or reject PO
     */
    public function approve() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check - Only Admin
        if (!Auth::isAdmin()) {
            return Response::forbidden('Only Admin can approve or reject purchase orders');
        }
        
        // Method check
        if (!Request::isPost() && !Request::isPatch()) {
            return Response::methodNotAllowed('POST, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        if (empty($input['action']) || !in_array($input['action'], ['approve', 'reject'])) {
            return Response::error('Action is required. Valid values: approve, reject', 400);
        }
        
        $po_id = intval($input['id']);
        $action = $input['action'];
        $reason = isset($input['reason']) ? trim($input['reason']) : null;
        
        // Get PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Must be Pending Approval
        if ($po['status'] !== 'Pending Approval') {
            return Response::error('Can only approve/reject POs with Pending Approval status', 400);
        }
        
        // Rejection requires reason
        if ($action === 'reject' && !$reason) {
            return Response::error('Rejection reason is required', 400);
        }
        
        // Approve or reject
        $result = PurchaseOrder::approveOrReject($po_id, $action, Auth::userId(), $reason);
        
        if ($result) {
            $message = $action === 'approve' 
                ? 'Purchase order approved successfully' 
                : 'Purchase order rejected';
            return Response::success($message, $result);
        } else {
            return Response::serverError('Failed to update purchase order status');
        }
    }
    
    /**
     * Mark PO as received
     */
    public function receive() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can mark purchase orders as received');
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        $po_id = intval($input['id']);
        $notes = isset($input['notes']) ? trim($input['notes']) : null;
        
        // Get PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $po['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to receive this purchase order');
        }
        
        // Must be Approved
        if ($po['status'] !== 'Approved') {
            return Response::error('Can only mark Approved POs as received', 400);
        }
        
        // Receive PO
        $result = PurchaseOrder::receive($po_id, $notes);
        
        if ($result) {
            return Response::success('Purchase order marked as received and stock updated successfully', $result);
        } else {
            return Response::serverError('Failed to receive purchase order');
        }
    }
    
    /**
     * Cancel purchase order
     */
    public function cancel() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can cancel purchase orders');
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        $po_id = intval($input['id']);
        $reason = isset($input['reason']) ? trim($input['reason']) : 'No reason provided';
        
        // Get PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Permission check - Asset Manager can only cancel their own
        if (!Auth::isAdmin() && ($po['campus_id'] != Auth::campusId() || $po['created_by'] != Auth::userId())) {
            return Response::forbidden('You can only cancel purchase orders you created');
        }
        
        // Can only cancel Draft or Pending Approval
        if (!in_array($po['status'], ['Draft', 'Pending Approval'])) {
            return Response::error('Can only cancel POs with Draft or Pending Approval status', 400);
        }
        
        // Cancel PO
        $cancelled = PurchaseOrder::cancel($po_id, $reason, Auth::userId());
        
        if ($cancelled) {
            return Response::success('Purchase order cancelled successfully', $cancelled);
        } else {
            return Response::serverError('Failed to cancel purchase order');
        }
    }
    
    /**
     * Delete purchase order
     */
    public function delete() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check - Only Admin
        if (!Auth::isAdmin()) {
            return Response::forbidden('Only Admin can delete purchase orders');
        }
        
        // Method check
        if (!Request::isDelete() && !Request::isPost()) {
            return Response::methodNotAllowed('DELETE, POST');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Purchase order ID is required', 400);
        }
        
        $po_id = intval($input['id']);
        
        // Get PO
        $po = PurchaseOrder::find($po_id);
        
        if (!$po) {
            return Response::notFound('Purchase order not found');
        }
        
        // Delete PO
        $deleted = PurchaseOrder::delete($po_id);
        
        if ($deleted) {
            return Response::success('Purchase order deleted successfully', [
                'po_id' => $po_id,
                'po_number' => $po['po_number']
            ]);
        } else {
            return Response::serverError('Failed to delete purchase order');
        }
    }
}
?>