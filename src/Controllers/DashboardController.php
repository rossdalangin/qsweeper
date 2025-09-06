<?php

namespace App\Controllers;

/**
 * Handles the main dashboard view after a user logs in.
 * Redirects users to the appropriate dashboard based on their role.
 */
class DashboardController extends Controller {

    /**
     * Ensures the user is logged in before showing the dashboard.
     */
    public function __construct() {
        $this->isLoggedIn();
    }

    /**
     * Fetches data and displays the correct dashboard for the logged-in user.
     * @return mixed
     */
    public function index() {
        $user = $_SESSION['user'];

        // Route to the correct dashboard based on role
        switch ($user['role']) {
            case 'admin':
                return view('admin/dashboard', [
                    'user' => $user,
                    'title' => 'Admin Dashboard'
                ]);
            case 'teacher':
                return view('teacher/dashboard', [
                    'user' => $user,
                    'title' => 'Teacher Dashboard'
                ]);
            case 'student':
                $db = \App\Core\Database::getInstance()->getConnection();
                $userId = $user['id'];

                // Get active games (games that are 'running' and the student is in a participating group)
                $stmt = $db->prepare(
                    "SELECT DISTINCT g.*
                     FROM games g
                     JOIN group_scores gs ON g.id = gs.game_id
                     JOIN group_members gm ON gs.group_id = gm.group_id
                     WHERE g.status = 'running' AND gm.user_id = :user_id"
                );
                $stmt->execute(['user_id' => $userId]);
                $activeGames = $stmt->fetchAll();

                // Get past games
                $stmt = $db->prepare(
                    "SELECT DISTINCT g.*
                     FROM games g
                     JOIN group_scores gs ON g.id = gs.game_id
                     JOIN group_members gm ON gs.group_id = gm.group_id
                     WHERE g.status = 'finished' AND gm.user_id = :user_id
                     ORDER BY g.finished_at DESC"
                );
                $stmt->execute(['user_id' => $userId]);
                $pastGames = $stmt->fetchAll();

                return view('student/dashboard', [
                    'user' => $user,
                    'activeGames' => $activeGames,
                    'pastGames' => $pastGames,
                    'title' => 'Student Dashboard'
                ]);
            default:
                // Should not happen, but as a fallback:
                session_destroy();
                header('Location: /login');
                exit();
        }
    }
}
