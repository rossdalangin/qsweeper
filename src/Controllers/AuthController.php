<?php

namespace App\Controllers;

use App\Core\Database;

class AuthController {

    public function create() {
        // Show the login form
        return view('auth/login', ['title' => 'Login']);
    }

    public function store() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            die('CSRF token validation failed.');
        }

        // Handle login attempt
        $db = Database::getInstance()->getConnection();

        // 1. Find the user by email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        $user = $stmt->fetch();

        // 2. Verify password
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            // 3. Start session and store user info
            session_start();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];

            // Redirect to the appropriate dashboard
            header('Location: /dashboard'); // A generic dashboard for now
            exit();
        }

        // Failed login
        // Later, we can add flash messages for errors
        return view('auth/login', ['error' => 'Invalid credentials.']);
    }

    public function destroy() {
        session_start();
        session_destroy();
        header('Location: /');
        exit();
    }
}
