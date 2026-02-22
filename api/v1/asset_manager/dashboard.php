<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Response.php';
require_once __DIR__ . '/../../../app/Models/Stock.php';
require_once __DIR__ . '/../../../app/Models/Asset.php';
require_once __DIR__ . '/../../../app/Models/PurchaseOrder.php';

if (!Auth::check()) {
    echo Response::unauthorized(); exit;
}
if (!Auth::hasRole(['Admin', 'Asset Manager'])) {
    echo Response::forbidden('Access denied'); exit;
}

try {
    $db = (new Database())->getConnection();

    // Low stock count
    $stmt = $db->prepare("SELECT COUNT(*) FROM stock WHERE quantity <= minimum_threshold");
    $stmt->execute();
    $low_stock_count = (int)$stmt->fetchColumn();

    // Total stock items
    $stmt = $db->prepare("SELECT COUNT(*) FROM stock");
    $stmt->execute();
    $total_stock = (int)$stmt->fetchColumn();

    // Assigned assets count
    $stmt = $db->prepare("SELECT COUNT(*) FROM assets WHERE status = 'In Use'");
    $stmt->execute();
    $assigned_count = (int)$stmt->fetchColumn();

    // Open POs count (not completed/cancelled)
    $stmt = $db->prepare("SELECT COUNT(*) FROM purchase_orders WHERE status NOT IN ('Completed','Cancelled')");
    $stmt->execute();
    $open_po_count = (int)$stmt->fetchColumn();

    // Top 5 low stock items
    $stmt = $db->prepare(
        "SELECT id, item_name, quantity, minimum_threshold, unit, category
         FROM stock
         WHERE quantity <= minimum_threshold
         ORDER BY (quantity - minimum_threshold) ASC
         LIMIT 5"
    );
    $stmt->execute();
    $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending approval POs
    $stmt = $db->prepare(
        "SELECT po.id, po.po_number, po.supplier, po.total_amount, po.status, po.created_at,
                u.name AS created_by_name
         FROM purchase_orders po
         LEFT JOIN users u ON u.id = po.created_by
         WHERE po.status = 'Pending Approval'
         ORDER BY po.created_at ASC
         LIMIT 5"
    );
    $stmt->execute();
    $pending_pos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo Response::success('Dashboard data retrieved', [
        'low_stock_count'  => $low_stock_count,
        'total_stock'      => $total_stock,
        'assigned_count'   => $assigned_count,
        'open_po_count'    => $open_po_count,
        'low_stock_items'  => $low_stock_items,
        'pending_pos'      => $pending_pos,
    ]);

} catch (Exception $e) {
    echo Response::serverError('Failed to load dashboard data');
}
?>