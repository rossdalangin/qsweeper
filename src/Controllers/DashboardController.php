<?php

namespace App\Controllers;

class DashboardController {

    public function __construct() {
        // Protect this controller
        // If user is not logged in, redirect to login
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    }

    public function index() {
        $user = $_SESSION['user'];

        // Route to the correct dashboard based on role
        switch ($user['role']) {
            case 'admin':
                return view('admin/dashboard', ['user' => $user]);
            case 'teacher':
                return view('teacher/dashboard', ['user' => $user]);
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
                    'pastGames' => $pastGames
                ]);
            default:
                // Should not happen, but as a fallback:
                session_destroy();
                header('Location: /login');
                exit();
        }
    }
}
