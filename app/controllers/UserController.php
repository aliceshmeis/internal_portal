<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/User.php';

class UserController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * List all users
     * GET /api/v1/users/list.php
     */
    public function list() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can view users');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $users = $this->userModel->getAll();

        if ($users === false) return Response::serverError('Failed to retrieve users');

        return Response::success('Users retrieved', $users, 200, ['count' => count($users)]);
    }

    /**
     * Create a new user
     * POST /api/v1/users/create.php
     */
    public function create() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Only Admins can create users');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

        // Validate
        if (empty($input['name']))      return Response::error('Full name is required', 400);
        if (empty($input['email']))     return Response::error('Email is required', 400);
        if (empty($input['password']))  return Response::error('Password is required', 400);
        if (empty($input['role']))      return Response::error('Role is required', 400);
        if (empty($input['campus_id'])) return Response::error('Campus is required', 400);

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL))
            return Response::error('Invalid email format', 400);

        if (strlen($input['password']) < 8)
            return Response::error('Password must be at least 8 characters', 400);

        $allowed_roles = ['Admin', 'Staff', 'Asset Manager', 'Viewer'];
        if (!in_array($input['role'], $allowed_roles))
            return Response::error('Invalid role', 400);

        // Check duplicate email
        if ($this->userModel->findByEmail($input['email']))
            return Response::error('A user with this email already exists', 409);

        $data = [
            'name'      => trim($input['name']),
            'email'     => strtolower(trim($input['email'])),
            'password'  => password_hash($input['password'], PASSWORD_BCRYPT),
            'role'      => $input['role'],
            'campus_id' => intval($input['campus_id']),
            'is_active' => isset($input['is_active']) ? intval($input['is_active']) : 1,
        ];

        $user = $this->userModel->createUser($data);

        if ($user) return Response::success('User created successfully', $user, 201);

        return Response::serverError('Failed to create user');
    }

    /**
     * Update a user
     * PUT /api/v1/users/update.php
     */
    public function update() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can update users');
        if (!Request::isPut()) return Response::methodNotAllowed('PUT');

        $input = Request::json();

        if (empty($input['id'])) return Response::error('User ID is required', 400);

        $user = $this->userModel->findById(intval($input['id']));
        if (!$user) return Response::notFound('User not found');

        $data = [];
        if (isset($input['name']))      $data['name']      = trim($input['name']);
        if (isset($input['role']))      $data['role']      = $input['role'];
        if (isset($input['campus_id'])) $data['campus_id'] = intval($input['campus_id']);
        if (isset($input['is_active'])) $data['is_active'] = intval($input['is_active']);

        if (!empty($input['password'])) {
            if (strlen($input['password']) < 8)
                return Response::error('Password must be at least 8 characters', 400);
            $data['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        if (empty($data)) return Response::error('No fields to update', 400);

        $updated = $this->userModel->updateUser(intval($input['id']), $data);

        if ($updated) return Response::success('User updated successfully', $updated);

        return Response::serverError('Failed to update user');
    }

    /**
     * Delete a user
     * DELETE /api/v1/users/delete.php
     */
    public function delete() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can delete users');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('User ID is required', 400);

        if (intval($input['id']) === Auth::userId())
            return Response::error('You cannot delete your own account', 400);

        $user = $this->userModel->findById(intval($input['id']));
        if (!$user) return Response::notFound('User not found');

        $deleted = $this->userModel->deleteUser(intval($input['id']));

        if ($deleted) return Response::success('User deleted successfully', null);

        return Response::serverError('Failed to delete user');
    }
}
?>