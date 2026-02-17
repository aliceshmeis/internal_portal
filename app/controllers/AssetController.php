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
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can create assets');
        }
        
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        $input = Request::json();
        
        $errors = [];
        if (empty($input['name']))     $errors[] = 'Asset name is required';
        if (empty($input['category'])) $errors[] = 'Category is required';
        
        if (!empty($errors)) {
            return Response::error('Validation failed', 400, $errors);
        }

        $allowed_categories = ['Laptop', 'Printer', 'Network Equipment', 'Furniture', 'Other'];
        if (!in_array($input['category'], $allowed_categories)) {
            return Response::error('Invalid category. Allowed: ' . implode(', ', $allowed_categories), 400);
        }

        $status = $input['status'] ?? 'Available';
        $allowed_statuses = ['Available', 'In Use', 'Maintenance', 'Retired'];
        if (!in_array($status, $allowed_statuses)) {
            return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed_statuses), 400);
        }
        
        $data = [
            'name'            => trim($input['name']),
            'category'        => $input['category'],
            'description'     => $input['description']     ?? null,
            'serial_number'   => $input['serial_number']   ?? null,
            'status'          => $status,
            'purchase_date'   => $input['purchase_date']   ?? null,
            'purchase_cost'   => isset($input['purchase_cost']) ? floatval($input['purchase_cost']) : null,
            'warranty_expiry' => $input['warranty_expiry'] ?? null,
            'campus_id'       => $input['campus_id']       ?? Auth::campusId(),
            'assigned_to'     => isset($input['assigned_to']) ? intval($input['assigned_to']) : null,
        ];
        
        $asset = Asset::create($data);
        
        if ($asset) {
            return Response::success('Asset created successfully', $asset, 201);
        }
        
        return Response::serverError('Failed to create asset');
    }
    
    /**
     * List all assets
     */
    public function list() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        $filters = [
            'category' => Request::get('category'),
            'status'   => Request::get('status'),
            'search'   => Request::get('search'),    // ← ADDED
        ];

        // Remove empty filters
        $filters = array_filter($filters);
        
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
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        $id = Request::get('id');
        if (!$id) {
            return Response::error('Asset ID is required', 400);
        }
        
        $asset = Asset::find($id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        if (!Auth::isAdmin() && $asset['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view this asset');
        }
        
        return Response::success('Asset retrieved successfully', $asset);
    }
    
    /**
     * Update asset
     */
    public function update() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can update assets');
        }
        
        if (!Request::isPut() && !Request::isPatch()) {
            return Response::methodNotAllowed('PUT, PATCH');
        }
        
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Asset ID is required', 400);
        }
        
        $asset_id = intval($input['id']);
        $asset = Asset::find($asset_id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        if (!Auth::isAdmin() && $asset['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this asset');
        }
        
        $updateData = [];
        
        if (isset($input['name']))            $updateData['name']            = trim($input['name']);
        if (isset($input['description']))     $updateData['description']     = trim($input['description']);
        if (isset($input['serial_number']))   $updateData['serial_number']   = trim($input['serial_number']);
        if (isset($input['purchase_date']))   $updateData['purchase_date']   = $input['purchase_date'];
        if (isset($input['purchase_cost']))   $updateData['purchase_cost']   = floatval($input['purchase_cost']);
        if (isset($input['warranty_expiry'])) $updateData['warranty_expiry'] = $input['warranty_expiry'];

        if (isset($input['assigned_to'])) {
            $updateData['assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;
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
        
        if (empty($updateData)) {
            return Response::error('No fields to update', 400);
        }
        
        $updated = Asset::update($asset_id, $updateData);
        
        if ($updated) {
            return Response::success('Asset updated successfully', $updated);
        }
        
        return Response::serverError('Failed to update asset');
    }
    
    /**
     * Assign asset to user
     * POST /api/v1/assets/assign.php
     */
    public function assign() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }

        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can assign assets');
        }

        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }

        $input = Request::json();

        if (empty($input['id']) || empty($input['user_id'])) {
            return Response::error('Asset ID and User ID are required', 400);
        }

        $asset = Asset::find(intval($input['id']));

        if (!$asset) {
            return Response::notFound('Asset not found');
        }

        if ($asset['status'] !== 'Available') {
            return Response::error('Asset is not available for assignment', 400);
        }

        $updated = Asset::assign(intval($input['id']), intval($input['user_id']));

        if ($updated) {
            return Response::success('Asset assigned successfully', $updated);
        }

        return Response::serverError('Failed to assign asset');
    }

    /**
     * Return asset (unassign)
     * POST /api/v1/assets/return.php
     */
    public function returnAsset() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }

        if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
            return Response::forbidden('Only Admin and Asset Manager can return assets');
        }

        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }

        $input = Request::json();

        if (empty($input['id'])) {
            return Response::error('Asset ID is required', 400);
        }

        $asset = Asset::find(intval($input['id']));

        if (!$asset) {
            return Response::notFound('Asset not found');
        }

        $updated = Asset::returnAsset(intval($input['id']));

        if ($updated) {
            return Response::success('Asset returned successfully', $updated);
        }

        return Response::serverError('Failed to return asset');
    }

    /**
     * Delete/retire asset
     */
    public function delete() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        if (!Auth::isAdmin()) {
            return Response::forbidden('Only Admin can delete assets');
        }
        
        if (!Request::isDelete() && !Request::isPost()) {
            return Response::methodNotAllowed('DELETE, POST');
        }
        
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Asset ID is required', 400);
        }
        
        $asset_id = intval($input['id']);
        $asset = Asset::find($asset_id);
        
        if (!$asset) {
            return Response::notFound('Asset not found');
        }
        
        $retired = Asset::delete($asset_id);
        
        if ($retired) {
            return Response::success('Asset retired successfully', $retired);
        }
        
        return Response::serverError('Failed to retire asset');
    }
}
?>