<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/PurchaseOrder.php';
require_once __DIR__ . '/../Models/Stock.php';

class DashboardController {
    
    /**
     * Get dashboard statistics
     */
    public function getStats() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        // Get stats based on role
        if (Auth::isAdmin()) {
            $stats = $this->getAdminStats();
        } else {
            $stats = $this->getCampusStats(Auth::campusId());
        }
        
        return Response::success('Dashboard stats retrieved successfully', $stats);
    }
    
    /**
     * Get admin dashboard stats (all campuses)
     */
    private function getAdminStats() {
        return [
            'tickets' => $this->getTicketStats(),
            'assets' => $this->getAssetStats(),
            'purchase_orders' => $this->getPurchaseOrderStats(),
            'stock' => $this->getStockStats(),
            'recent_tickets' => $this->getRecentTickets(10),
            'low_stock_items' => $this->getLowStockItems()
        ];
    }
    
    /**
     * Get campus-specific dashboard stats
     */
    private function getCampusStats($campus_id) {
        return [
            'tickets' => $this->getTicketStats($campus_id),
            'assets' => $this->getAssetStats($campus_id),
            'recent_tickets' => $this->getRecentTickets(10, $campus_id)
        ];
    }
    
    /**
     * Get ticket statistics
     */
    private function getTicketStats($campus_id = null) {
        $tickets = $campus_id ? Ticket::getByCampus($campus_id) : Ticket::getAll();
        
        $stats = [
            'total' => count($tickets),
            'by_status' => [
                'Open' => 0,
                'In Progress' => 0,
                'Pending' => 0,
                'Resolved' => 0,
                'Closed' => 0
            ],
            'by_priority' => [
                'Low' => 0,
                'Medium' => 0,
                'High' => 0,
                'Critical' => 0
            ]
        ];
        
        foreach ($tickets as $ticket) {
            if (isset($stats['by_status'][$ticket['status']])) {
                $stats['by_status'][$ticket['status']]++;
            }
            if (isset($stats['by_priority'][$ticket['priority']])) {
                $stats['by_priority'][$ticket['priority']]++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get asset statistics
     */
    private function getAssetStats($campus_id = null) {
        $assets = $campus_id ? Asset::getByCampus($campus_id) : Asset::getAll();
        
        $stats = [
            'total' => count($assets),
            'by_status' => [
                'Available' => 0,
                'In Use' => 0,
                'Maintenance' => 0,
                'Retired' => 0
            ],
            'by_category' => [
                'Laptop' => 0,
                'Printer' => 0,
                'Network Equipment' => 0,
                'Furniture' => 0,
                'Other' => 0
            ]
        ];
        
        foreach ($assets as $asset) {
            if (isset($stats['by_status'][$asset['status']])) {
                $stats['by_status'][$asset['status']]++;
            }
            if (isset($stats['by_category'][$asset['category']])) {
                $stats['by_category'][$asset['category']]++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get purchase order statistics
     */
    private function getPurchaseOrderStats($campus_id = null) {
        $pos = $campus_id ? PurchaseOrder::getByCampus($campus_id) : PurchaseOrder::getAll();
        
        $stats = [
            'total' => count($pos),
            'by_status' => [
                'Pending' => 0,
                'Approved' => 0,
                'Rejected' => 0,
                'Completed' => 0
            ],
            'total_amount' => 0,
            'pending_amount' => 0
        ];
        
        foreach ($pos as $po) {
            if (isset($stats['by_status'][$po['status']])) {
                $stats['by_status'][$po['status']]++;
            }
            $stats['total_amount'] += floatval($po['total_amount']);
            
            if ($po['status'] === 'Pending') {
                $stats['pending_amount'] += floatval($po['total_amount']);
            }
        }
        
        return $stats;
    }
    
    /**
     * Get stock statistics
     */
    private function getStockStats($campus_id = null) {
        $stock_items = $campus_id ? Stock::getByCampus($campus_id) : Stock::getAll();
        
        $stats = [
            'total_items' => count($stock_items),
            'low_stock_count' => 0,
            'out_of_stock_count' => 0,
            'total_value' => 0
        ];
        
        foreach ($stock_items as $item) {
            $quantity = intval($item['quantity']);
            $min_threshold = intval($item['minimum_threshold']);
            
            if ($quantity == 0) {
                $stats['out_of_stock_count']++;
            } elseif ($quantity <= $min_threshold) {
                $stats['low_stock_count']++;
            }
            
            if (isset($item['unit_cost'])) {
                $stats['total_value'] += $quantity * floatval($item['unit_cost']);
            }
        }
        
        return $stats;
    }
    
    /**
     * Get recent tickets
     */
    private function getRecentTickets($limit = 10, $campus_id = null) {
        $tickets = $campus_id ? Ticket::getByCampus($campus_id) : Ticket::getAll();
        
        // Sort by created_at descending
        usort($tickets, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($tickets, 0, $limit);
    }
    
    /**
     * Get low stock items
     */
    private function getLowStockItems($campus_id = null) {
        $stock_items = $campus_id ? Stock::getByCampus($campus_id) : Stock::getAll();
        
        $low_stock = [];
        
        foreach ($stock_items as $item) {
            $quantity = intval($item['quantity']);
            $min_threshold = intval($item['minimum_threshold']);
            
            if ($quantity <= $min_threshold) {
                $low_stock[] = $item;
            }
        }
        
        return $low_stock;
    }
    
    /**
     * Get activity log (recent changes)
     */
    public function getRecentActivity() {
        // Auth check
        if (!Auth::check()) {
            return Response::unauthorized();
        }
        
        // Method check
        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }
        
        $limit = Request::get('limit') ?? 20;
        
        // This would require an activity_log table
        // For now, return recent tickets and recent assets as activity
        $recent = [
            'tickets' => $this->getRecentTickets(5),
            'message' => 'Activity log not yet implemented. Showing recent tickets instead.'
        ];
        
        return Response::success('Recent activity retrieved', $recent);
    }
}
?>