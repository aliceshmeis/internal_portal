<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/database.php'; 

class UserController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Only Admins can view users');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        // Always scope by campus — admin sees only users in their campus
        $users = $this->userModel->getByCampus(Auth::campusId());

        if ($users === false) return Response::serverError('Failed to retrieve users');

        return Response::success('Users retrieved', $users, 200, ['count' => count($users)]);
    }

    public function create() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Only Admins can create users');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

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

        // Admin can only create users in their own campus
        if (intval($input['campus_id']) !== Auth::campusId())
            return Response::forbidden('You can only create users for your own campus');

        if ($this->userModel->findByEmail($input['email']))
            return Response::error('A user with this email already exists', 409);

        $data = [
            'name'          => trim($input['name']),
            'email'         => strtolower(trim($input['email'])),
            'password'      => password_hash($input['password'], PASSWORD_BCRYPT),
            'role'          => $input['role'],
            'campus_id'     => intval($input['campus_id']),
            'department_id' => !empty($input['department_id']) ? intval($input['department_id']) : null,
            'is_active'     => isset($input['is_active']) ? intval($input['is_active']) : 1,
        ];

        $user = $this->userModel->createUser($data);
        if ($user) return Response::success('User created successfully', $user, 201);
        return Response::serverError('Failed to create user');
    }

    public function updateUser() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can update users');

        $input = Request::json();

        if (empty($input['id']))    return Response::error('User ID is required', 400);
        if (empty($input['name']))  return Response::error('Name is required', 400);
        if (empty($input['email'])) return Response::error('Email is required', 400);
        if (empty($input['role']))  return Response::error('Role is required', 400);

        $id   = intval($input['id']);
        $user = $this->userModel->findById($id);
        if (!$user) return Response::notFound('User not found');

        // Admin can only update users in their campus
        if ($user['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only update users in your campus');

        if ($id === Auth::userId() && $input['role'] !== 'Admin')
            return Response::error('You cannot change your own role', 400);

        $data = [
            'name'          => trim($input['name']),
            'email'         => strtolower(trim($input['email'])),
            'role'          => $input['role'],
            'campus_id'     => !empty($input['campus_id'])     ? intval($input['campus_id'])     : null,
            'department_id' => !empty($input['department_id']) ? intval($input['department_id']) : null,
            'is_active'     => isset($input['status']) ? ($input['status'] === 'Active' ? 1 : 0) : $user['is_active'],
        ];

        if (!empty($input['password'])) {
            if (strlen($input['password']) < 8)
                return Response::error('Password must be at least 8 characters', 400);
            $data['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        $updated = $this->userModel->updateUser($id, $data);
        if ($updated) return Response::success('User updated successfully', $updated);
        return Response::serverError('Failed to update user');
    }

    public function itList() {
    if (!Auth::check())    return Response::unauthorized();
    if (!Auth::isAdmin())  return Response::forbidden('Access denied');
    if (!Request::isGet()) return Response::methodNotAllowed('GET');

    try {
        $db   = (new Database())->getConnection();
        $stmt = $db->prepare(
            "SELECT u.id, u.name, u.email, u.role, c.campus_name
             FROM users u
             LEFT JOIN campuses c    ON c.id = u.campus_id
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE u.is_active = 1
               AND u.campus_id = :campus_id
               AND d.name = 'IT'
             ORDER BY u.name ASC"
        );
        $stmt->execute([':campus_id' => Auth::campusId()]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return Response::success('IT staff retrieved', $users, 200, ['count' => count($users)]);
    } catch (Exception $e) {
        return Response::serverError('Failed to retrieve IT staff');
    }
}

    public function deleteUser() {
        if (!Auth::check())   return Response::unauthorized();
        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can delete users');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('User ID is required', 400);

        $id = intval($input['id']);
        if ($id === Auth::userId())
            return Response::error('You cannot delete your own account', 400);

        $user = $this->userModel->findById($id);
        if (!$user) return Response::notFound('User not found');

        // Admin can only delete users in their campus
        if ($user['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only delete users in your campus');

        $deleted = $this->userModel->deleteUser($id);
        if ($deleted) return Response::success('User deleted successfully', null);
        return Response::serverError('Failed to delete user');
    }
}
?>