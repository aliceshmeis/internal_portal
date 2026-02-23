<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../Models/Ticket.php';
require_once __DIR__ . '/../Models/Asset.php';
require_once __DIR__ . '/../Models/PurchaseOrder.php';
require_once __DIR__ . '/../Models/Stock.php';

class DashboardController {

    public function getStats() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        // Everyone — Admin, Asset Manager, Staff — sees only their campus
        $stats = $this->getCampusStats(Auth::campusId());

        return Response::success('Dashboard stats retrieved successfully', $stats);
    }

    private function getCampusStats($campus_id) {
        return [
            'tickets'          => $this->getTicketStats($campus_id),
            'assets'           => $this->getAssetStats($campus_id),
            'purchase_orders'  => $this->getPurchaseOrderStats($campus_id),
            'stock'            => $this->getStockStats($campus_id),
            'recent_tickets'   => $this->getRecentTickets(10, $campus_id),
            'low_stock_items'  => $this->getLowStockItems($campus_id),
        ];
    }

    private function getTicketStats($campus_id) {
        $tickets = Ticket::getByCampus($campus_id);

        $stats = [
            'total'       => count($tickets),
            'by_status'   => ['Open' => 0, 'In Progress' => 0, 'Pending' => 0, 'Resolved' => 0, 'Closed' => 0],
            'by_priority' => ['Low' => 0, 'Medium' => 0, 'High' => 0, 'Critical' => 0],
        ];

        foreach ($tickets as $ticket) {
            if (isset($stats['by_status'][$ticket['status']]))     $stats['by_status'][$ticket['status']]++;
            if (isset($stats['by_priority'][$ticket['priority']])) $stats['by_priority'][$ticket['priority']]++;
        }

        return $stats;
    }

    private function getAssetStats($campus_id) {
        $assets = Asset::getByCampus($campus_id);

        $stats = [
            'total'       => count($assets),
            'by_status'   => ['Available' => 0, 'In Use' => 0, 'Maintenance' => 0, 'Retired' => 0],
            'by_category' => ['Laptop' => 0, 'Printer' => 0, 'Network Equipment' => 0, 'Furniture' => 0, 'Other' => 0],
        ];

        foreach ($assets as $asset) {
            if (isset($stats['by_status'][$asset['status']]))     $stats['by_status'][$asset['status']]++;
            if (isset($stats['by_category'][$asset['category']])) $stats['by_category'][$asset['category']]++;
        }

        return $stats;
    }

    private function getPurchaseOrderStats($campus_id) {
        $pos = PurchaseOrder::getByCampus($campus_id);

        $stats = [
            'total'          => count($pos),
            'by_status'      => ['Pending Approval' => 0, 'Approved' => 0, 'Rejected' => 0, 'Completed' => 0],
            'total_amount'   => 0,
            'pending_amount' => 0,
        ];

        foreach ($pos as $po) {
            if (isset($stats['by_status'][$po['status']])) $stats['by_status'][$po['status']]++;
            $stats['total_amount'] += floatval($po['total_amount']);
            if ($po['status'] === 'Pending Approval') $stats['pending_amount'] += floatval($po['total_amount']);
        }

        return $stats;
    }

    private function getStockStats($campus_id) {
        $stock_items = Stock::getByCampus($campus_id);

        $stats = [
            'total_items'       => count($stock_items),
            'low_stock_count'   => 0,
            'out_of_stock_count'=> 0,
            'total_value'       => 0,
        ];

        foreach ($stock_items as $item) {
            $qty = intval($item['quantity']);
            $min = intval($item['minimum_threshold']);
            if ($qty == 0)        $stats['out_of_stock_count']++;
            elseif ($qty <= $min) $stats['low_stock_count']++;
            if (isset($item['unit_cost'])) $stats['total_value'] += $qty * floatval($item['unit_cost']);
        }

        return $stats;
    }

    private function getRecentTickets($limit = 10, $campus_id = null) {
        $tickets = Ticket::getByCampus($campus_id);
        usort($tickets, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        return array_slice($tickets, 0, $limit);
    }

    private function getLowStockItems($campus_id = null) {
        $stock_items = Stock::getByCampus($campus_id);
        $low_stock   = [];
        foreach ($stock_items as $item) {
            if (intval($item['quantity']) <= intval($item['minimum_threshold'])) {
                $low_stock[] = $item;
            }
        }
        return $low_stock;
    }

    public function getRecentActivity() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        $recent = [
            'tickets' => $this->getRecentTickets(5, Auth::campusId()),
            'message' => 'Activity log not yet implemented. Showing recent tickets instead.'
        ];

        return Response::success('Recent activity retrieved', $recent);
    }
}
?>