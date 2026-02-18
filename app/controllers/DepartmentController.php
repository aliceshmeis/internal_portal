<?php
class DepartmentController {

    public function list() {
        if (!Auth::check()) {
            return Response::unauthorized();
        }

        if (!Request::isGet()) {
            return Response::methodNotAllowed('GET');
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            $campus_id = Request::get('campus_id');

            if ($campus_id) {
                $stmt = $db->prepare("
                    SELECT id, name, description
                    FROM departments
                    WHERE campus_id = :campus_id
                    AND is_active = 1
                    ORDER BY name ASC
                ");
                $stmt->bindParam(':campus_id', $campus_id, PDO::PARAM_INT);
            } else {
                $stmt = $db->prepare("
                    SELECT id, name, description
                    FROM departments
                    WHERE is_active = 1
                    ORDER BY name ASC
                ");
            }

            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return Response::success('Departments retrieved successfully', $departments, 200, [
                'count' => count($departments)
            ]);

        } catch (Exception $e) {
            return Response::serverError('Failed to retrieve departments: ' . $e->getMessage());
        }
    }
}
?>