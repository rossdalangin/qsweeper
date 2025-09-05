<?php

namespace App\Controllers;

class Controller {
    protected function isAdmin() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            // You can redirect, show a 403 page, or throw an exception
            header('HTTP/1.0 403 Forbidden');
            die('You are not authorized to perform this action.');
        }
    }

    protected function isTeacher() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
            header('HTTP/1.0 403 Forbidden');
            die('You are not authorized to perform this action.');
        }
    }

    protected function isLoggedIn() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    }
}
