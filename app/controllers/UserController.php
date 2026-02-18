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
        'name'          => trim($input['name']),
        'email'         => strtolower(trim($input['email'])),
        'password'      => password_hash($input['password'], PASSWORD_BCRYPT),
        'role'          => $input['role'],
        'campus_id'     => intval($input['campus_id']),
        'department_id' => !empty($input['department_id']) ? intval($input['department_id']) : null,  // ← ADD THIS
        'is_active'     => isset($input['is_active']) ? intval($input['is_active']) : 1,
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

    public function updateUser() {
    if (!Auth::check()) {
        return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ($_SESSION['role'] !== 'Admin') {
        return Response::json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $data = Request::getBody(); // parsed JSON body

    $id         = isset($data['id'])         ? (int)$data['id']                    : 0;
    $name       = isset($data['name'])       ? trim($data['name'])                  : '';
    $email      = isset($data['email'])      ? trim($data['email'])                 : '';
    $role       = isset($data['role'])       ? trim($data['role'])                  : '';
    $campus_id  = isset($data['campus_id'])  ? (int)$data['campus_id']             : null;
    $dept_id    = isset($data['department_id']) ? (int)$data['department_id']      : null;
    $status     = isset($data['status'])     ? trim($data['status'])                : 'Active';

    if (!$id || !$name || !$email || !$role) {
        return Response::json(['success' => false, 'message' => 'Missing required fields']);
    }

    $user = new User();
    $result = $user->updateUser($id, $name, $email, $role, $campus_id, $dept_id, $status);

    if ($result) {
        return Response::json(['success' => true, 'message' => 'User updated successfully']);
    }
    return Response::json(['success' => false, 'message' => 'Failed to update user']);
}

public function deleteUser() {
    if (!Auth::check()) {
        return Response::json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ($_SESSION['role'] !== 'Admin') {
        return Response::json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $data = Request::getBody();
    $id   = isset($data['id']) ? (int)$data['id'] : 0;

    if (!$id) {
        return Response::json(['success' => false, 'message' => 'User ID required']);
    }

    // Prevent admin from deleting themselves
    if ($id === (int)$_SESSION['user_id']) {
        return Response::json(['success' => false, 'message' => 'You cannot delete your own account']);
    }

    $user   = new User();
    $result = $user->deleteUser($id);

    if ($result) {
        return Response::json(['success' => true, 'message' => 'User deleted successfully']);
    }
    return Response::json(['success' => false, 'message' => 'Failed to delete user']);
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