<?php

namespace App\Controllers;

use App\Core\Database;

/**
 * Handles user authentication (login, logout).
 */
class AuthController extends Controller {

    /**
     * Displays the login form.
     * @return mixed
     */
    public function create() {
        return view('auth/login', ['title' => 'Login']);
    }

    /**
     * Processes the login form submission.
     */
    public function store() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            die('CSRF token validation failed.');
        }

        $db = Database::getInstance()->getConnection();

        // 1. Find the user by email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        $user = $stmt->fetch();

        // 2. Verify password
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            // 3. Start session and store user info
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];

            // Redirect to the appropriate dashboard
            header('Location: /dashboard');
            exit();
        }

        // Failed login
        return view('auth/login', [
            'title' => 'Login',
            'error' => 'Invalid credentials.'
        ]);
    }

    /**
     * Destroys the user session (logout).
     */
    public function destroy() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');
        session_destroy();
        header('Location: /');
        exit();
    }
}
