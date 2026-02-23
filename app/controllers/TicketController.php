<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../models/Ticket.php';

class TicketController {

    public function create() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

        if (empty($input['title']))       return Response::error('Title is required', 400);
        if (empty($input['description'])) return Response::error('Description is required', 400);

        $allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];
        $priority = $input['priority'] ?? 'Medium';
        if (!in_array($priority, $allowed_priorities))
            return Response::error('Invalid priority. Allowed: ' . implode(', ', $allowed_priorities), 400);

        $campus_id = $input['campus_id'] ?? Auth::campusId();
        if (empty($campus_id)) return Response::error('Campus not found for this user', 400);

        $data = [
            'title'       => trim($input['title']),
            'description' => trim($input['description']),
            'priority'    => $priority,
            'campus_id'   => $campus_id,
            'created_by'  => Auth::userId(),
            'category'    => $input['category'] ?? null,
            'building'    => !empty($input['building']) ? trim($input['building']) : null,
            'floor'       => !empty($input['floor'])    ? trim($input['floor'])    : null,
            'room'        => !empty($input['room'])     ? trim($input['room'])     : null,
            'ssid'        => !empty($input['ssid'])     ? trim($input['ssid'])     : null,
        ];

        if (isset($input['assigned_to'])) {
            if (!Auth::isAdmin())
                return Response::forbidden('Only Admins can assign tickets during creation');
            if (!is_numeric($input['assigned_to']))
                return Response::error('assigned_to must be a valid user ID', 400);
            $data['assigned_to'] = intval($input['assigned_to']);
        }

        $ticket = Ticket::create($data);
        if ($ticket) return Response::success('Ticket created successfully', $ticket, 201);
        return Response::serverError('Failed to create ticket');
    }

    public function myTickets() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');
        $tickets = Ticket::getByUser(Auth::userId());
        return Response::success('Tickets retrieved', $tickets);
    }

    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $filters = [
            'status'   => Request::get('status'),
            'priority' => Request::get('priority')
        ];

        // Always scope by campus — admin sees only their campus
        $tickets = Ticket::getByCampus(Auth::campusId(), $filters);

        return Response::success('Tickets retrieved successfully', $tickets, 200, [
            'count' => count($tickets)
        ]);
    }

    public function show() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $id = Request::get('id');
        if (!$id) return Response::error('Ticket ID is required', 400);

        $ticket = Ticket::find($id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to view this ticket');

        return Response::success('Ticket retrieved successfully', $ticket);
    }

    public function update() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isPut() && !Request::isPatch()) return Response::methodNotAllowed('PUT, PATCH');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Ticket ID is required', 400);

        $ticket_id = intval($input['id']);
        $ticket    = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if (!Auth::isAdmin()) {
            $isCreator  = ($ticket['created_by']  == Auth::userId());
            $isAssigned = ($ticket['assigned_to'] == Auth::userId());
            if (!$isCreator && !$isAssigned)
                return Response::forbidden('You can only update tickets you created or are assigned to');
            if ($ticket['campus_id'] != Auth::campusId())
                return Response::forbidden('You do not have permission to update this ticket');
            if (isset($input['status']) && in_array($input['status'], ['Resolved', 'Closed']))
                return Response::forbidden('Staff cannot mark tickets as Resolved or Closed via update.');
            if (isset($input['assigned_to']))
                return Response::forbidden('Staff cannot assign tickets. Only Admins can assign tickets.');
        } else {
            // Admin can only update tickets in their campus
            if ($ticket['campus_id'] != Auth::campusId())
                return Response::forbidden('You do not have permission to update tickets outside your campus');
        }

        $updateData = [];
        if (isset($input['title']))       $updateData['title']       = trim($input['title']);
        if (isset($input['description'])) $updateData['description'] = trim($input['description']);

        if (isset($input['status'])) {
            $allowed = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];
            if (!in_array($input['status'], $allowed))
                return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed), 400);
            $updateData['status'] = $input['status'];
        }

        if (isset($input['priority'])) {
            $allowed = ['Low', 'Medium', 'High', 'Critical'];
            if (!in_array($input['priority'], $allowed))
                return Response::error('Invalid priority. Allowed: ' . implode(', ', $allowed), 400);
            $updateData['priority'] = $input['priority'];
        }

        if (isset($input['assigned_to']) && Auth::isAdmin())
            $updateData['assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;

        if (empty($updateData)) return Response::error('No fields to update', 400);

        $updated = Ticket::update($ticket_id, $updateData);
        if ($updated) return Response::success('Ticket updated successfully', $updated);
        return Response::serverError('Failed to update ticket');
    }

    public function staffUpdate() {
        if (!Auth::check())    return Response::unauthorized();
        if (Auth::isAdmin())   return Response::error('Admins should use the regular update endpoint', 400);
        if (!Request::isPost() && !Request::isPut()) return Response::methodNotAllowed('POST, PUT');

        $input = Request::json();
        if (empty($input['ticket_id'])) return Response::error('Ticket ID is required', 400);
        if (empty($input['comment']))   return Response::error('Comment is required', 400);

        $ticket_id = intval($input['ticket_id']);
        $ticket    = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        $isCreator  = ($ticket['created_by']  == Auth::userId());
        $isAssigned = ($ticket['assigned_to'] == Auth::userId());
        if (!$isCreator && !$isAssigned)
            return Response::forbidden('You can only update tickets you created or are assigned to');
        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to update this ticket');
        if ($ticket['status'] === 'Closed')
            return Response::error('Cannot update closed tickets', 400);

        $new_comment = Ticket::addComment($ticket_id, Auth::userId(), trim($input['comment']));
        if (!$new_comment) return Response::serverError('Failed to add comment');

        $updated_ticket = Ticket::update($ticket_id, ['status' => 'Pending']);
        if (!$updated_ticket) return Response::serverError('Failed to update ticket status');

        return Response::success('Ticket updated and comment added successfully', [
            'ticket'  => $updated_ticket,
            'comment' => $new_comment
        ]);
    }

    public function close() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isPost() && !Request::isPatch()) return Response::methodNotAllowed('POST, PATCH');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Ticket ID is required', 400);

        $ticket_id = intval($input['id']);
        $ticket    = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if (!Auth::isAdmin()) return Response::forbidden('Only Admins can close tickets');

        // Admin can only close tickets in their campus
        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only close tickets in your campus');

        if ($ticket['status'] === 'Closed') return Response::error('Ticket is already closed', 400);

        $closed = Ticket::close($ticket_id);
        if ($closed) return Response::success('Ticket closed successfully', $closed);
        return Response::serverError('Failed to close ticket');
    }

    public function assign() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Auth::hasRole(['Admin', 'Staff'])) return Response::forbidden('Only Admin and Staff can assign tickets');
        if (!Request::isPost() && !Request::isPatch()) return Response::methodNotAllowed('POST, PATCH');

        $input = Request::json();
        if (empty($input['ticket_id']))   return Response::error('Ticket ID is required', 400);
        if (empty($input['assigned_to'])) return Response::error('User ID to assign to is required', 400);

        $ticket_id   = intval($input['ticket_id']);
        $assigned_to = intval($input['assigned_to']);

        $ticket = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only assign tickets in your campus');

        if ($ticket['status'] === 'Closed')
            return Response::error('Cannot assign closed tickets. Reopen the ticket first.', 400);

        $assigned = Ticket::assign($ticket_id, $assigned_to);
        if ($assigned) return Response::success('Ticket assigned successfully', $assigned);
        return Response::serverError('Failed to assign ticket');
    }

    public function resolve() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isPost() && !Request::isPatch()) return Response::methodNotAllowed('POST, PATCH');

        $input = Request::json();
        if (empty($input['id'])) return Response::error('Ticket ID is required', 400);

        $ticket_id        = intval($input['id']);
        $resolution_notes = $input['resolution_notes'] ?? null;

        $ticket = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if (!Auth::isAdmin() && $ticket['assigned_to'] != Auth::userId())
            return Response::forbidden('Only the assigned user or Admin can resolve this ticket');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You can only resolve tickets in your campus');

        if ($ticket['status'] === 'Closed') return Response::error('Ticket is already closed', 400);

        $resolved = Ticket::resolve($ticket_id, $resolution_notes);
        if ($resolved) return Response::success('Ticket marked as resolved', $resolved);
        return Response::serverError('Failed to resolve ticket');
    }

    public function upload() {
        if (!Auth::check())     return Response::unauthorized();
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
        if (!$ticket_id) return Response::error('Ticket ID required', 400);
        if (empty($_FILES['attachments'])) return Response::error('No files uploaded', 400);

        $upload_dir = __DIR__ . '/../../../storage/attachments/' . $ticket_id . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $allowed  = ['image/png','image/jpeg','image/gif','application/pdf',
                     'application/msword',
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                     'text/plain'];
        $max_size = 5 * 1024 * 1024;
        $uploaded = [];
        $errors   = [];
        $files    = $_FILES['attachments'];
        $count    = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $name  = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
            $tmp   = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $size  = is_array($files['size'])     ? $files['size'][$i]     : $files['size'];
            $error = is_array($files['error'])    ? $files['error'][$i]    : $files['error'];

            if ($error !== UPLOAD_ERR_OK)  { $errors[] = "$name: Upload error";      continue; }
            if ($size > $max_size)         { $errors[] = "$name: Exceeds 5MB limit"; continue; }

            $finfo     = finfo_open(FILEINFO_MIME_TYPE);
            $real_type = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            if (!in_array($real_type, $allowed)) { $errors[] = "$name: Type not allowed"; continue; }

            $ext       = pathinfo($name, PATHINFO_EXTENSION);
            $safe_name = uniqid('attach_') . '.' . strtolower($ext);
            $dest      = $upload_dir . $safe_name;
            $web_path  = '/internal_portal/storage/attachments/' . $ticket_id . '/' . $safe_name;

            if (!move_uploaded_file($tmp, $dest)) { $errors[] = "$name: Failed to save"; continue; }

            TicketAttachment::create([
                'ticket_id'   => $ticket_id,
                'file_name'   => $name,
                'file_path'   => $web_path,
                'file_size'   => $size,
                'file_type'   => $real_type,
                'uploaded_by' => Auth::userId()
            ]);

            $uploaded[] = ['name' => $name, 'path' => $web_path, 'size' => $size];
        }

        return json_encode([
            'success'  => count($uploaded) > 0,
            'message'  => count($uploaded) . ' file(s) uploaded' . (count($errors) ? ', ' . count($errors) . ' failed' : ''),
            'uploaded' => $uploaded,
            'errors'   => $errors
        ]);
    }

    public function addComment() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isPost()) return Response::methodNotAllowed('POST');

        $input = Request::json();

        $errors = [];
        if (empty($input['ticket_id'])) $errors[] = 'Ticket ID is required';
        if (empty($input['comment']))   $errors[] = 'Comment is required';
        if (!empty($errors))            return Response::error('Validation failed', 400, $errors);

        $ticket_id = intval($input['ticket_id']);
        $ticket    = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to comment on this ticket');

        if ($ticket['status'] === 'Closed' && !Auth::isAdmin())
            return Response::error('Cannot comment on closed tickets', 400);

        $new_comment = Ticket::addComment($ticket_id, Auth::userId(), trim($input['comment']));
        if ($new_comment) return Response::success('Comment added successfully', $new_comment, 201);
        return Response::serverError('Failed to add comment');
    }

    public function getComments() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $ticket_id = Request::get('ticket_id');
        if (!$ticket_id) return Response::error('Ticket ID is required', 400);

        $ticket = Ticket::find($ticket_id);
        if (!$ticket) return Response::notFound('Ticket not found');

        if ($ticket['campus_id'] != Auth::campusId())
            return Response::forbidden('You do not have permission to view comments for this ticket');

        $comments = Ticket::getComments($ticket_id);
        return Response::success('Comments retrieved successfully', $comments, 200, ['count' => count($comments)]);
    }

    public function getStats() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        // Always scope by campus
        $stats = Ticket::getStatsByStatus(Auth::campusId());
        return Response::success('Stats retrieved', $stats);
    }
}
?>