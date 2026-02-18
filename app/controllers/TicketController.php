<?php
require_once __DIR__ . '/../../core/Auth.php';//checks if user logged in
require_once __DIR__ . '/../../core/Response.php';//sends JSON responses in a standard format (success, error, unauthorized, forbidden...).
require_once __DIR__ . '/../../core/Request.php';////reads request method (GET/POST/PUT/PATCH) and gets inputs (query params / JSON body)
require_once __DIR__ . '/../models/Ticket.php';

class TicketController {
    
    /**
     * Create a new ticket - UPDATED to support assigned_to for Admins
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
        
        // ========================================
        // Handle assigned_to field (Admin only)
        // ========================================
        if (isset($input['assigned_to'])) {
            if (!Auth::isAdmin()) {
                return Response::forbidden('Only Admins can assign tickets during creation');
            }
            
            // Validate assigned_to is a number
            if (!is_numeric($input['assigned_to'])) {
                return Response::error('assigned_to must be a valid user ID (number)', 400);
            }
            
            $data['assigned_to'] = intval($input['assigned_to']);
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
            'status' => Request::get('status'),//This reads a value from the URL query string.
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
        
        // SECURITY: Permission check
        if (!Auth::isAdmin()) {
            // Staff can only view tickets in their campus
            if ($ticket['campus_id'] != Auth::campusId()) {
                return Response::forbidden('You do not have permission to view this ticket');
            }
        }
        
        return Response::success('Ticket retrieved successfully', $ticket);
    }
    
    /**
     * Update ticket - FIXED SECURITY
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
        
        // ========================================
        // SECURITY: Different rules for Admin vs Staff
        // ========================================
        if (!Auth::isAdmin()) {
            $isCreator = ($ticket['created_by'] == Auth::userId());
            $isAssigned = ($ticket['assigned_to'] == Auth::userId());
            
            // Staff can only update if they created it OR are assigned to it
            if (!$isCreator && !$isAssigned) {
                return Response::forbidden('You can only update tickets you created or are assigned to');
            }
            
            // Extra security: Check campus too
            if ($ticket['campus_id'] != Auth::campusId()) {
                return Response::forbidden('You do not have permission to update this ticket');
            }
            
            // Staff cannot change status to Resolved or Closed via update
            if (isset($input['status']) && in_array($input['status'], ['Resolved', 'Closed'])) {
                return Response::forbidden('Staff cannot mark tickets as Resolved or Closed via update. Please use the resolve/close endpoints.');
            }
            
            // Staff cannot assign tickets (only Admin)
            if (isset($input['assigned_to'])) {
                return Response::forbidden('Staff cannot assign tickets. Only Admins can assign tickets.');
            }
            
            // For staff: Recommend using the staff-update endpoint instead
            // This endpoint is mainly for Admins
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
        
        // Only Admin can assign
        if (isset($input['assigned_to']) && Auth::isAdmin()) {
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
     * Staff Update - Simplified endpoint for staff
     * Staff can only add comments, status automatically becomes Pending
     */
    public function staffUpdate() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Only Staff can use this (Admin should use full update)
        if (Auth::isAdmin()) {
            return Response::error('Admins should use the regular update endpoint', 400);
        }
        
        // Method check
        if (!Request::isPost() && !Request::isPut()) {
            return Response::methodNotAllowed('POST, PUT');
        }
        
        // Get input
        $input = Request::json();
        
        // Validate
        if (empty($input['ticket_id'])) {
            return Response::error('Ticket ID is required', 400);
        }
        
        if (empty($input['comment'])) {
            return Response::error('Comment is required', 400);
        }
        
        $ticket_id = intval($input['ticket_id']);
        $comment = trim($input['comment']);
        
        // Get ticket
        $ticket = Ticket::find($ticket_id);
        
        if (!$ticket) {
            return Response::notFound('Ticket not found');
        }
        
        // SECURITY: Staff can only update tickets they're assigned to OR created
        $isCreator = ($ticket['created_by'] == Auth::userId());
        $isAssigned = ($ticket['assigned_to'] == Auth::userId());
        
        if (!$isCreator && !$isAssigned) {
            return Response::forbidden('You can only update tickets you created or are assigned to');
        }
        
        // Check campus
        if ($ticket['campus_id'] != Auth::campusId()) {
            return Response::forbidden('You do not have permission to update this ticket');
        }
        
        // Can't update closed tickets
        if ($ticket['status'] === 'Closed') {
            return Response::error('Cannot update closed tickets', 400);
        }
        
        // Add comment
        $new_comment = Ticket::addComment($ticket_id, Auth::userId(), $comment);
        
        if (!$new_comment) {
            return Response::serverError('Failed to add comment');
        }
        
        // Update status to Pending (automatic for staff updates)
        $updated_ticket = Ticket::update($ticket_id, ['status' => 'Pending']);
        
        if (!$updated_ticket) {
            return Response::serverError('Failed to update ticket status');
        }
        
        return Response::success('Ticket updated and comment added successfully', [
            'ticket' => $updated_ticket,
            'comment' => $new_comment
        ], 200);
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
        
        // SECURITY: Only Admin can close tickets
        if (!Auth::isAdmin()) {
            return Response::forbidden('Only Admins can close tickets');
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
        
        // Permission check - Only Admin and Staff
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
        
        // SECURITY: Only assigned user or Admin can resolve
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