<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/Subtask.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';

class SubtaskController {

    // POST api/v1/subtasks/create.php
    public function create() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Auth::isAdmin())   return Response::forbidden('Only admins can create subtasks');
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['ticket_id'])) return Response::error('ticket_id is required', 400);
        if (empty($input['title']))     return Response::error('title is required', 400);

        $ticket = Ticket::find(intval($input['ticket_id']));
        if (!$ticket) return Response::notFound('Ticket not found');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only manage tickets in your campus');

        $priority = $input['priority'] ?? $ticket['priority'];
        if (!in_array($priority, ['Low','Medium','High','Critical']))
            $priority = $ticket['priority'];

        $user_ids = [];
        if (!empty($input['user_ids']) && is_array($input['user_ids']))
            $user_ids = array_map('intval', $input['user_ids']);

        $subtask = Subtask::create([
            'ticket_id'   => intval($input['ticket_id']),
            'title'       => trim($input['title']),
            'description' => !empty($input['description']) ? trim($input['description']) : null,
            'priority'    => $priority,
            'due_date'    => !empty($input['due_date']) ? $input['due_date'] : null,
            'created_by'  => Auth::userId(),
            'user_ids'    => $user_ids,
        ]);

        if ($subtask) {
    // Change parent ticket status to In Progress if it's still Open
    if ($ticket['status'] === 'Open') {
        Ticket::update(intval($input['ticket_id']), ['status' => 'In Progress']);
    }
    return Response::success('Subtask created successfully', $subtask, 201);
}
        return Response::serverError('Failed to create subtask');
    }

    // GET api/v1/subtasks/list.php?ticket_id=X
    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $ticket_id = intval(Request::get('ticket_id'));
        if (!$ticket_id) return Response::error('ticket_id is required', 400);

        $ticket = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        $all = Subtask::getByTicket($ticket_id);

        if (!Auth::isAdmin()) {
            $user_id    = Auth::userId();
            $is_primary = ($ticket['assigned_to'] == $user_id || $ticket['created_by'] == $user_id);
            if (!$is_primary) {
                // Non-admin only sees subtasks they are explicitly assigned to
                $all = array_values(array_filter($all, function($s) use ($user_id) {
                    foreach ($s['assignments'] as $a)
                        if ($a['user_id'] == $user_id) return true;
                    return false;
                }));
                // Strip comments from subtasks this user is not assigned to
                foreach ($all as &$s) {
                    // Comments already filtered — they can see all comments on their own subtasks
                }
            }
        }

        return Response::success('Subtasks retrieved', $all, 200, ['count' => count($all)]);
    }

    // GET api/v1/subtasks/my-subtasks.php
    public function mySubtasks() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $subtasks = Subtask::getByUser(Auth::userId());
        return Response::success('Subtasks retrieved', $subtasks, 200, ['count' => count($subtasks)]);
    }

    // POST api/v1/subtasks/update-status.php
    public function updateStatus() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['subtask_id']))  return Response::error('subtask_id is required', 400);
        if (empty($input['user_status'])) return Response::error('user_status is required', 400);

        $allowed = ['Assigned', 'In Progress', 'Done'];
        if (!in_array($input['user_status'], $allowed))
            return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed), 400);

        $subtask_id = intval($input['subtask_id']);
        $user_id    = Auth::userId();

        if (!Subtask::isUserAssigned($subtask_id, $user_id))
            return Response::forbidden('You are not assigned to this subtask');

        $updated = Subtask::updateUserStatus($subtask_id, $user_id, $input['user_status']);
        if ($updated) return Response::success('Status updated successfully', $updated);
        return Response::serverError('Failed to update status');
    }

    // POST api/v1/subtasks/comments/create.php
    public function addComment() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();
        if (empty($input['subtask_id'])) return Response::error('subtask_id is required', 400);
        if (empty($input['comment']))    return Response::error('comment is required', 400);

        $subtask_id = intval($input['subtask_id']);
        $user_id    = Auth::userId();

        $subtask = Subtask::find($subtask_id);
        if (!$subtask) return Response::notFound('Subtask not found');

        // Admin can always comment; others must be assigned
        if (!Auth::isAdmin() && !Subtask::isUserAssigned($subtask_id, $user_id))
            return Response::forbidden('You are not assigned to this subtask');

        $comment = Subtask::addComment($subtask_id, $user_id, trim($input['comment']));
        if ($comment) return Response::success('Comment added', $comment, 201);
        return Response::serverError('Failed to add comment');
    }

    // GET api/v1/subtasks/comments/list.php?subtask_id=X
    public function listComments() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $subtask_id = intval(Request::get('subtask_id'));
        if (!$subtask_id) return Response::error('subtask_id is required', 400);

        $subtask = Subtask::find($subtask_id);
        if (!$subtask) return Response::notFound('Subtask not found');

        if (!Auth::isAdmin() && !Subtask::isUserAssigned($subtask_id, Auth::userId()))
            return Response::forbidden('Access denied');

        $comments = Subtask::getComments($subtask_id);
        return Response::success('Comments retrieved', $comments, 200, ['count' => count($comments)]);
    }

    // GET api/v1/subtasks/departments.php?type=academic|administrative
    public function departmentsByType() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Admins only');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $type = Request::get('type');
        if (!in_array($type, ['academic', 'administrative']))
            return Response::error('type must be academic or administrative', 400);

        $departments = Subtask::getDepartmentsByType(Auth::campusId(), $type);
        return Response::success('Departments retrieved', $departments, 200, ['count' => count($departments)]);
    }

    // GET api/v1/subtasks/users-by-department.php?department_id=X
    public function usersByDepartment() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::isAdmin())  return Response::forbidden('Admins only');
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $department_id = intval(Request::get('department_id'));
        if (!$department_id) return Response::error('department_id is required', 400);

        $users = Subtask::getUsersByDepartment($department_id, Auth::campusId());
        return Response::success('Users retrieved', $users, 200, ['count' => count($users)]);
    }
    // POST api/v1/subtasks/update.php
public function update() {
    if (!Auth::check())     return Response::unauthorized();
    if (!Auth::isAdmin())   return Response::forbidden('Only admins can update subtasks');
    if (!Request::isPost()) return Response::methodNotAllowed('POST');//REST rules

    $input = Request::json();//reads JSON body from frontend.
    if (empty($input['subtask_id'])) return Response::error('subtask_id is required', 400);
    if (empty($input['title']))      return Response::error('title is required', 400);

    $subtask = Subtask::find(intval($input['subtask_id']));//fetch the subtask from database.
    if (!$subtask) return Response::notFound('Subtask not found');//So no updating something that doesn’t exist.

    $ticket = Ticket::find($subtask['ticket_id']);//You find the parent ticket of this subtask.
    if ($ticket['campus_id'] != Auth::campusId())//IF ticket campus is NOT equal to user campus
        return Response::forbidden('You can only manage tickets in your campus');

        //If user_ids exist AND is array:
        //convert each value to integer (security)
        //store it, otherwise null

    $user_ids = !empty($input['user_ids']) && is_array($input['user_ids'])
        ? array_map('intval', $input['user_ids'])
        : null;

    $updated = Subtask::update(intval($input['subtask_id']), [//call model to update
        'title'       => trim($input['title']),
        'description' => !empty($input['description']) ? trim($input['description']) : null,
        'priority'    => $input['priority'] ?? $subtask['priority'],
        'due_date'    => $input['due_date']  ?? null,
        'user_ids'    => $user_ids,
    ]);

    if ($updated) return Response::success('Subtask updated successfully', $updated);
    return Response::serverError('Failed to update subtask');
}

// POST api/v1/subtasks/delete.php
public function delete() {
    if (!Auth::check())     return Response::unauthorized();
    if (!Auth::isAdmin())   return Response::forbidden('Only admins can delete subtasks');
    if (!Request::isPost()) return Response::methodNotAllowed('POST');

    $input = Request::json();
    if (empty($input['subtask_id'])) return Response::error('subtask_id is required', 400);

    $subtask = Subtask::find(intval($input['subtask_id']));
    if (!$subtask) return Response::notFound('Subtask not found');

    $ticket = Ticket::find($subtask['ticket_id']);
    if ($ticket['campus_id'] != Auth::campusId())
        return Response::forbidden('You can only manage tickets in your campus');

    $deleted = Subtask::delete(intval($input['subtask_id']));
    if ($deleted) return Response::success('Subtask deleted successfully', null);
    return Response::serverError('Failed to delete subtask');
}
}