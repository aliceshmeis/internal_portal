<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Stock.php';

class StockController {

    /**
     * Create a new stock item
     */
    public function create() {
        if (!Auth::check())                           return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can create stock items');
        if (!Request::isPost())                        return Response::methodNotAllowed('POST');

        $input = Request::json();

        $errors = [];
        if (empty($input['item_name']))                          $errors[] = 'Item name is required';
        if (!isset($input['quantity']) || !is_numeric($input['quantity'])) $errors[] = 'Valid quantity is required';
        if (!empty($errors))                                     return Response::error('Validation failed', 400, $errors);

        $data = [
            'item_name'         => trim($input['item_name']),
            'category'          => isset($input['category'])  ? trim($input['category'])  : null,
            'sku'               => isset($input['sku'])        ? trim($input['sku'])        : null,
            'quantity'          => intval($input['quantity']),
            'unit'              => isset($input['unit'])       ? trim($input['unit'])       : 'units',
            'minimum_threshold' => isset($input['minimum_threshold']) ? intval($input['minimum_threshold']) : 10,
            'unit_cost'         => isset($input['unit_cost'])  ? floatval($input['unit_cost']) : 0,
            'campus_id'         => Auth::campusId()
        ];

        if ($data['quantity'] < 0) return Response::error('Quantity cannot be negative', 400);

        $stock = Stock::create($data);

        if ($stock) {
            $is_low_stock = ($stock['quantity'] <= $stock['minimum_threshold']);
            $alert = $is_low_stock ? 'WARNING: Stock quantity is at or below minimum threshold!' : null;
            return Response::success('Stock item created successfully', $stock, 201, ['alert' => $alert]);
        }

        return Response::error('Stock item with this name already exists. Use Adjust instead.', 400);
    }

    /**
     * List all stock items
     */
    public function list() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $filters = [
            'category'  => Request::get('category'),
            'low_stock' => Request::get('low_stock') === 'true'
        ];

        $stock_items = Auth::isAdmin()
            ? Stock::getAll($filters)
            : Stock::getByCampus(Auth::campusId(), $filters);

        $low_stock_count = 0;
        foreach ($stock_items as $item) {
            if ($item['is_low_stock'] == 1) $low_stock_count++;
        }

        $alert = $low_stock_count > 0 ? "$low_stock_count item(s) are low on stock!" : null;

        return Response::success('Stock items retrieved successfully', $stock_items, 200, [
            'count'           => count($stock_items),
            'low_stock_count' => $low_stock_count,
            'alert'           => $alert
        ]);
    }

    /**
     * Show single stock item
     */
    public function show() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $id = Request::get('id');
        if (!$id) return Response::error('Stock ID is required', 400);

        $stock = Stock::find($id);
        if (!$stock) return Response::notFound('Stock item not found');

        if (!Auth::isAdmin() && $stock['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view this stock item');
        }

        $alert = $stock['is_low_stock'] == 1 ? 'WARNING: This item is low on stock!' : null;
        return Response::success('Stock item retrieved successfully', $stock, 200, ['alert' => $alert]);
    }

    /**
     * Update stock item — accepts POST or PUT/PATCH
     */
    public function update() {
        if (!Auth::check())                           return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Asset Manager'])) return Response::forbidden('Only Admin and Asset Manager can update stock');

        $input = Request::json();

        if (empty($input['id'])) return Response::error('Stock ID is required', 400);

        $stock_id = intval($input['id']);
        $stock    = Stock::find($stock_id);

        if (!$stock) return Response::notFound('Stock item not found');

        if (!Auth::isAdmin() && $stock['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this stock item');
        }

        $updateData = [];

        if (isset($input['item_name']))         $updateData['item_name']         = trim($input['item_name']);
        if (isset($input['category']))          $updateData['category']          = trim($input['category']);
        if (isset($input['sku']))               $updateData['sku']               = trim($input['sku']);
        if (isset($input['unit']))              $updateData['unit']              = trim($input['unit']);
        if (isset($input['unit_cost']))         $updateData['unit_cost']         = floatval($input['unit_cost']);

        if (isset($input['quantity'])) {
            if (!is_numeric($input['quantity']) || $input['quantity'] < 0)
                return Response::error('Quantity must be a non-negative number', 400);
            $updateData['quantity'] = intval($input['quantity']);
        }

        if (isset($input['minimum_threshold'])) {
            if (!is_numeric($input['minimum_threshold']) || $input['minimum_threshold'] < 0)
                return Response::error('Minimum threshold must be a non-negative number', 400);
            $updateData['minimum_threshold'] = intval($input['minimum_threshold']);
        }

        if (empty($updateData)) return Response::error('No fields to update', 400);

        $updated = Stock::update($stock_id, $updateData);

        if ($updated) {
            $alert = $updated['is_low_stock'] == 1
                ? "⚠️ ALERT: Stock ({$updated['quantity']} {$updated['unit']}) is at or below minimum threshold ({$updated['minimum_threshold']} {$updated['unit']})!"
                : null;
            return Response::success('Stock updated successfully', $updated, 200, ['alert' => $alert]);
        }

        return Response::serverError('Failed to update stock');
    }

    /**
     * Delete stock item
     */
    public function delete() {
        if (!Auth::check())  return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admin can delete stock items');

        $input    = Request::json();
        if (empty($input['id'])) return Response::error('Stock ID is required', 400);

        $stock_id = intval($input['id']);
        $stock    = Stock::find($stock_id);
        if (!$stock) return Response::notFound('Stock item not found');

        $deleted = Stock::delete($stock_id);

        if ($deleted) {
            return Response::success('Stock item deleted successfully', [
                'stock_id'  => $stock_id,
                'item_name' => $stock['item_name'],
                'deleted'   => true
            ]);
        }

        return Response::serverError('Failed to delete stock item');
    }
}
?>