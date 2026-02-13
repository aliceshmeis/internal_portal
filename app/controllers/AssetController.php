<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Asset.php';

class AssetController {
    
    /**
     * Create a new asset
     */
    public function create() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can create assets');
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        $errors = [];
        if (empty($input['name'])) {
            $errors[] = 'Asset name is required';
        }
        if (empty($input['category'])) {
            $errors[] = 'Category is required';
        }
        
        if (!empty($errors)) {
            return Response::error('Validation failed', 400, $errors);
        }
        
        // Prepare data
        $data = [
            'name' => trim($input['name']),
            'category' => $input['category'],
            'description' => isset($input['description']) ? trim($input['description']) : null,
            'serial_number' => isset($input['serial_number']) ? trim($input['serial_number']) : null,
            'status' => isset($input['status']) ? $input['status'] : 'Available',
            'purchase_date' => isset($input['purchase_date']) ? $input['purchase_date'] : null,
            'purchase_cost' => isset($input['purchase_cost']) ? floatval($input['purchase_cost']) : null,
            'warranty_expiry' => isset($input['warranty_expiry']) ? $input['warranty_expiry'] : null,
            'campus_id' => Auth::campusId()
        ];
        
        // Validate category
        $allowed_categories = ['Laptop', 'Printer', 'Network Equipment', 'Furniture', 'Other'];
        if (!in_array($data['category'], $allowed_categories)) {
            return Response::error('Invalid category. Allowed: ' . implode(', ', $allowed_categories), 400);
        }
        
        // Validate status
        $allowed_statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];
        if (!in_array($data['status'], $allowed_statuses)) {
            return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed_statuses), 400);
        }
        
        // Create asset
        $asset = Asset::create($data);
        
        if ($asset) {
            return Response::success('Asset created successfully', $asset, 201);
        } else {
            return Response::serverError('Failed to create asset');
        }
    }
    
    /**
     * List all assets
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
            'category' => Request::get('category'),
            'status' => Request::get('status')
        ];
        
        // Get assets based on role
        if (Auth::isAdmin()) {
            $assets = Asset::getAll($filters);
        } else {
            $assets = Asset::getByCampus(Auth::campusId(), $filters);
        }
        
        return Response::success('Assets retrieved successfully', $assets, 200, [
            'count' => count($assets)
        ]);
    }
    
    /**
     * Show single asset
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
            return Response::error('Asset ID is required', 400);
        }
        
        // Get asset
        $asset = Asset::find($id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $asset['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view this asset');
        }
        
        return Response::success('Asset retrieved successfully', $asset);
    }
    
    /**
     * Update asset
     */
    public function update() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can update assets');
        }
        
        // Method check
        if (!Request::isPut() && !Request::isPatch()) {
            return Response::methodNotAllowed('PUT, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Asset ID is required', 400);
        }
        
        $asset_id = intval($input['id']);
        
        // Get existing asset
        $asset = Asset::find($asset_id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $asset['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this asset');
        }
        
        // Build update data
        $updateData = [];
        
        if (isset($input['name'])) {
            $updateData['name'] = trim($input['name']);
        }
        
        if (isset($input['description'])) {
            $updateData['description'] = trim($input['description']);
        }
        
        if (isset($input['serial_number'])) {
            $updateData['serial_number'] = trim($input['serial_number']);
        }
        
        if (isset($input['category'])) {
            $allowed_categories = ['Laptop', 'Printer', 'Network Equipment', 'Furniture', 'Other'];
            if (!in_array($input['category'], $allowed_categories)) {
                return Response::error('Invalid category. Allowed: ' . implode(', ', $allowed_categories), 400);
            }
            $updateData['category'] = $input['category'];
        }
        
        if (isset($input['status'])) {
            $allowed_statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];
            if (!in_array($input['status'], $allowed_statuses)) {
                return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed_statuses), 400);
            }
            $updateData['status'] = $input['status'];
        }
        
        if (isset($input['assigned_to'])) {
            $updateData['assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;
        }
        
        if (isset($input['purchase_date'])) {
            $updateData['purchase_date'] = $input['purchase_date'];
        }
        
        if (isset($input['purchase_cost'])) {
            $updateData['purchase_cost'] = floatval($input['purchase_cost']);
        }
        
        if (isset($input['warranty_expiry'])) {
            $updateData['warranty_expiry'] = $input['warranty_expiry'];
        }
        
        if (empty($updateData)) {
            return Response::error('No fields to update', 400);
        }
        
        // Update asset
        $updated = Asset::update($asset_id, $updateData);
        
        if ($updated) {
            return Response::success('Asset updated successfully', $updated);
        } else {
            return Response::serverError('Failed to update asset');
        }
    }
    
    /**
     * Delete/retire asset
     */
    public function delete() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check - Only Admin
        if (!Auth::isAdmin()) {
            return Response::forbidden('Only Admin can delete assets');
        }
        
        // Method check
        if (!Request::isDelete() && !Request::isPost()) {
            return Response::methodNotAllowed('DELETE, POST');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Asset ID is required', 400);
        }
        
        $asset_id = intval($input['id']);
        
        // Get asset
        $asset = Asset::find($asset_id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        // Retire asset
        $retired = Asset::delete($asset_id);
        
        if ($retired) {
            return Response::success('Asset retired successfully (soft delete)', $retired);
        } else {
            return Response::serverError('Failed to retire asset');
        }
    }
}
?>