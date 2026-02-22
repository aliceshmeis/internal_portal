<?php
/**
 * IT Ticket Controller
 * File: app/controllers/ITTicketController.php
 */

require_once __DIR__ . '/../models/Ticket.php';

class ITTicketController
{
    private Ticket $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    // ─── GET STATS ────────────────────────────────────────────────────────────
    public function getStats(): array
    {
        return $this->ticket->getStatusCounts();
    }

    // ─── LIST TICKETS ─────────────────────────────────────────────────────────
    public function list(array $filters = []): array
    {
        return $this->ticket->getAll($filters);
    }

    // ─── SHOW ONE TICKET ──────────────────────────────────────────────────────
    public function show(int $id): ?array
    {
        return $this->ticket->findById($id);
    }

    // ─── UPDATE STATUS ────────────────────────────────────────────────────────
    /**
     * Update a ticket's status.
     * - Admins can update any ticket.
     * - IT staff can ONLY update tickets assigned to them by admin.
     *
     * @return array ['success' => bool, 'message' => string, 'code' => int]
     */
    public function updateStatus(int $ticketId, string $newStatus, int $userId): array
    {
        $allowedStatuses = ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'];

        if (!in_array($newStatus, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Invalid status value.', 'code' => 400];
        }

        $ticket = $this->ticket->findById($ticketId);

        if (!$ticket) {
            return ['success' => false, 'message' => 'Ticket not found.', 'code' => 404];
        }

        // Assignment guard — skip for admins
        $role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';

        if (strtolower($role) !== 'admin') {
            $assignedTo = (int)($ticket['assigned_to'] ?? 0);

            if ($assignedTo === 0 || $assignedTo !== $userId) {
                return [
                    'success' => false,
                    'message' => 'You cannot update this ticket. It has not been assigned to you by an admin.',
                    'code'    => 403,
                ];
            }
        }

        $updated = $this->ticket->updateStatus($ticketId, $newStatus);

        if ($updated) {
            return ['success' => true, 'message' => 'Status updated successfully.', 'code' => 200];
        }

        return ['success' => false, 'message' => 'Failed to update status.', 'code' => 500];
    }
}