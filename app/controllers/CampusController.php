<?php
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';

class CampusController {

    public function list() {
        if (!Auth::check())    return Response::unauthorized();
        if (!Request::isGet()) return Response::methodNotAllowed('GET');

        try {
            $database = new Database();
            $db       = $database->getConnection();

            $stmt = $db->prepare("
                SELECT id, campus_name, campus_code, location
                FROM campuses
                WHERE is_active = 1
                ORDER BY campus_name ASC
            ");
            $stmt->execute();
            $campuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return Response::success('Campuses retrieved', $campuses, 200, [
                'count' => count($campuses)
            ]);

        } catch (Exception $e) {
            return Response::serverError('Failed to retrieve campuses');
        }
    }
}
?>