<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Supplier.php';

class SupplierController {

    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Admins only');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $suppliers = Supplier::getAll();
        return Response::success('Suppliers retrieved', $suppliers, 200, ['count' => count($suppliers)]);
    }

    public function create() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['name'])) return Response::error('Supplier name is required', 400);

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL))
            return Response::error('Invalid email format', 400);

        $supplier = Supplier::create($input);
        if ($supplier) return Response::success('Supplier created', $supplier, 201);
        return Response::serverError('Failed to create supplier');
    }

    public function update() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['id']))   return Response::error('id is required', 400);
        if (empty($input['name'])) return Response::error('name is required', 400);

        $supplier = Supplier::find(intval($input['id']));
        if (!$supplier) return Response::notFound('Supplier not found');

        $updated = Supplier::update(intval($input['id']), $input);
        if ($updated) return Response::success('Supplier updated', $updated);
        return Response::serverError('Failed to update supplier');
    }

    public function deactivate() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Admins only');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('id is required', 400);

        $ok = Supplier::deactivate(intval($input['id']));
        if ($ok) return Response::success('Supplier deactivated', null);
        return Response::serverError('Failed');
    }
}
?>