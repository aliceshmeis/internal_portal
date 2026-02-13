<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Ticket.php';

class TicketController {
    
    /**
     * Create a new ticket
     */
    public function create() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        $errors = [];
        if (empty($input['title'])) {
            $errors[] = 'Title is required';
        }
        if (empty($input['description'])) {
            $errors[] = 'Description is required';
        }
        
        if (!empty($errors)) {
            return Response::error('Validation failed', 400, $errors);
        }
        
        // Prepare data
        $data = [
            'title' => trim($input['title']),
            'description' => trim($input['description']),
            'priority' => $input['priority'] ?? 'Medium',
            'campus_id' => Auth::campusId(),
            'created_by' => Auth::userId()
        ];
        
        // Validate priority
        $allowed_priorities = ['Low', 'Medium', 'High', 'Critical'];
        if (!in_array($data['priority'], $allowed_priorities)) {
            return Response::error('Invalid priority. Allowed: ' . implode(', ', $allowed_priorities), 400);
        }
        
        // Create ticket
        $ticket = Ticket::create($data);
        
        if ($ticket) {
            return Response::success('Ticket created successfully', $ticket, 201);
        } else {
            return Response::serverError('Failed to create ticket');
        }
    }
    
    /**
     * List all tickets
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
            'priority' => Request::get('priority')
        ];
        
        // Get tickets based on role
        if (Auth::isAdmin()) {
            $tickets = Ticket::getAll($filters);
        } else {
            $tickets = Ticket::getByCampus(Auth::campusId(), $filters);
        }
        
        return Response::success('Tickets retrieved successfully', $tickets, 200, [
            'count' => count($tickets)
        ]);
    }
    
    /**
     * Show single ticket
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
            return Response::error('Ticket ID is required', 400);
        }
        
        // Get ticket
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check - Staff can only see their campus
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view this ticket');
        }
        
        return Response::success('Ticket retrieved successfully', $ticket);
    }
    
    /**
     * Update ticket
     */
    public function update() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isPut() && !Request::isPatch()) {
            return Response::methodNotAllowed('PUT, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate ID
        if (empty($input['id'])) {
            return Response::error('Ticket ID is required', 400);
        }
        
        $ticket_id = intval($input['id']);
        
        // Get existing ticket
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this ticket');
        }
        
        // Build update data
        $updateData = [];
        
        if (isset($input['title'])) {
            $updateData['title'] = trim($input['title']);
        }
        
        if (isset($input['description'])) {
            $updateData['description'] = trim($input['description']);
        }
        
        if (isset($input['status'])) {
            $allowed = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];
            if (!in_array($input['status'], $allowed)) {
                return Response::error('Invalid status. Allowed: ' . implode(', ', $allowed), 400);
            }
            $updateData['status'] = $input['status'];
        }
        
        if (isset($input['priority'])) {
            $allowed = ['Low', 'Medium', 'High', 'Critical'];
            if (!in_array($input['priority'], $allowed)) {
                return Response::error('Invalid priority. Allowed: ' . implode(', ', $allowed), 400);
            }
            $updateData['priority'] = $input['priority'];
        }
        
        if (isset($input['assigned_to'])) {
            $updateData['assigned_to'] = $input['assigned_to'] ? intval($input['assigned_to']) : null;
        }
        
        if (empty($updateData)) {
            return Response::error('No fields to update', 400);
        }
        
        // Update ticket
        $updated = Ticket::update($ticket_id, $updateData);
        
        if ($updated) {
            return Response::success('Ticket updated successfully', $updated);
        } else {
            return Response::serverError('Failed to update ticket');
        }
    }
    
    /**
     * Close ticket
     */
    public function close() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isPost() && !Request::isPatch()) {
            return Response::methodNotAllowed('POST, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Ticket ID is required', 400);
        }
        
        $ticket_id = intval($input['id']);
        
        // Get ticket
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to close this ticket');
        }
        
        // Check if already closed
        if ($ticket['status'] === 'Closed') {
            return Response::error('Ticket is already closed', 400);
        }
        
        // Close ticket
        $closed = Ticket::close($ticket_id);
        
        if ($closed) {
            return Response::success('Ticket closed successfully', $closed);
        } else {
            return Response::serverError('Failed to close ticket');
        }
    }
    
    /**
     * Assign ticket to user
     */
    public function assign() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Permission check
        if (!Auth::hasRole(['Admin', 'Staff'])) {
            return Response::forbidden('Only Admin and Staff can assign tickets');
        }
        
        // Method check
        if (!Request::isPost() && !Request::isPatch()) {
            return Response::methodNotAllowed('POST, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        if (empty($input['ticket_id'])) {
            return Response::error('Ticket ID is required', 400);
        }
        
        if (empty($input['assigned_to'])) {
            return Response::error('User ID to assign to is required', 400);
        }
        
        $ticket_id = intval($input['ticket_id']);
        $assigned_to = intval($input['assigned_to']);
        
        // Get ticket
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check - Staff can only assign in their campus
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('Staff can only assign tickets in their campus');
        }
        
        // Can't assign closed tickets
        if ($ticket['status'] === 'Closed') {
            return Response::error('Cannot assign closed tickets. Reopen the ticket first.', 400);
        }
        
        // Assign ticket
        $assigned = Ticket::assign($ticket_id, $assigned_to);
        
        if ($assigned) {
            return Response::success('Ticket assigned successfully', $assigned);
        } else {
            return Response::serverError('Failed to assign ticket');
        }
    }
    
    /**
     * Resolve ticket
     */
    public function resolve() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isPost() && !Request::isPatch()) {
            return Response::methodNotAllowed('POST, PATCH');
        }
        
        // Get input
        $input = Request::json();
        
        if (empty($input['id'])) {
            return Response::error('Ticket ID is required', 400);
        }
        
        $ticket_id = intval($input['id']);
        $resolution_notes = $input['resolution_notes'] ?? null;
        
        // Get ticket
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check - Only assigned user or Admin
        if (!Auth::isAdmin() && $ticket['assigned_to'] != Auth::userId()) {
            return Response::forbidden('Only the assigned user or Admin can resolve this ticket');
        }
        
        // Can't resolve closed tickets
        if ($ticket['status'] === 'Closed') {
            return Response::error('Ticket is already closed', 400);
        }
        
        // Resolve ticket
        $resolved = Ticket::resolve($ticket_id, $resolution_notes);
        
        if ($resolved) {
            return Response::success('Ticket marked as resolved', $resolved);
        } else {
            return Response::serverError('Failed to resolve ticket');
        }
    }
    
    /**
     * Add comment to ticket
     */
    public function addComment() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isPost()) {
            return Response::methodNotAllowed('POST');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        $errors = [];
        if (empty($input['ticket_id'])) {
            $errors[] = 'Ticket ID is required';
        }
        if (empty($input['comment'])) {
            $errors[] = 'Comment is required';
        }
        
        if (!empty($errors)) {
            return Response::error('Validation failed', 400, $errors);
        }
        
        $ticket_id = intval($input['ticket_id']);
        $comment = trim($input['comment']);
        
        // Check if ticket exists
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to comment on this ticket');
        }
        
        // Can't comment on closed tickets (unless Admin)
        if ($ticket['status'] === 'Closed' && !Auth::isAdmin()) {
            return Response::error('Cannot comment on closed tickets', 400);
        }
        
        // Add comment
        $new_comment = Ticket::addComment($ticket_id, Auth::userId(), $comment);
        
        if ($new_comment) {
            return Response::success('Comment added successfully', $new_comment, 201);
        } else {
            return Response::serverError('Failed to add comment');
        }
    }
    
    /**
     * Get comments for a ticket
     */
    public function getComments() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        // Get ticket ID
        $ticket_id = Request::get('ticket_id');
        
        if (!$ticket_id) {
            return Response::error('Ticket ID is required', 400);
        }
        
        // Check if ticket exists
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // Permission check
        if (!Auth::isAdmin() && $ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to view comments for this ticket');
        }
        
        // Get comments
        $comments = Ticket::getComments($ticket_id);
        
        return Response::success('Comments retrieved successfully', $comments, 200, [
            'count' => count($comments)
        ]);
    }
}
?>