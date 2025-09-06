<?php

namespace App\Controllers;

/**
 * Base Controller
 * Provides common functionality for all controllers.
 */
class Controller {
    /**
     * Checks if the current user is an admin.
     * If not, it terminates the request with a 403 Forbidden error.
     */
    protected function isAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            die('You are not authorized to perform this action.');
        }
    }

    /**
     * Checks if the current user is a teacher.
     * If not, it terminates the request with a 403 Forbidden error.
     */
    protected function isTeacher() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
            header('HTTP/1.0 403 Forbidden');
            die('You are not authorized to perform this action.');
        }
    }

    /**
     * Checks if a user is logged in.
     * If not, it redirects them to the login page.
     */
    protected function isLoggedIn() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    }
}
