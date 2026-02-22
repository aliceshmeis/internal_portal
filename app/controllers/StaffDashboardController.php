<?php
class StaffDashboardController {

    public function index() {
        if (!Auth::check()) return Response::unauthorized();

        $userId = Auth::userId();

        $my_tickets = Ticket::getByUser($userId);
        $my_assets  = Asset::getByUser($userId);

        return Response::success('Dashboard loaded', [
            'my_tickets' => $my_tickets,
            'my_assets'  => $my_assets
        ]);
    }
}
?>