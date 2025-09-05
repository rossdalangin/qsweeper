<?php

namespace App\Controllers;

use App\Core\Database;

class AdminController extends Controller {

    public function __construct() {
        $this->isAdmin();
    }

    public function index() {
        // The main admin dashboard is still at /dashboard, which is fine.
        // This controller handles specific admin sections like user management.
        // We can add more to this later.
        header('Location: /admin/users');
        exit();
    }

    public function usersDestroy() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['id'])) {
            die('User ID is required.');
        }

        // Prevent admin from deleting their own account
        if ($_POST['id'] == $_SESSION['user']['id']) {
            die('You cannot delete your own account.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $_POST['id']]);

        header('Location: /admin/users');
        exit();
    }

    public function usersEdit() {
        if (!isset($_GET['id'])) {
            die('User ID is required.');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            die('User not found.');
        }

        return view('admin/users/edit', ['user' => $user]);
    }

    public function usersUpdate() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        if (empty($_POST['id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['role'])) {
            die('All fields except password are required.');
        }

        $db = Database::getInstance()->getConnection();

        // Check if email is being changed to one that already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $_POST['email'], 'id' => $_POST['id']]);
        if ($stmt->fetch()) {
            die('Email already in use by another account.');
        }

        // Prepare the update query
        $sql = "UPDATE users SET name = :name, email = :email, role = :role";
        $params = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'role' => $_POST['role'],
            'id' => $_POST['id']
        ];

        // If password is provided, hash and add it to the update
        if (!empty($_POST['password'])) {
            $params['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql .= ", password_hash = :password_hash";
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        header('Location: /admin/users');
        exit();
    }

    public function usersIndex() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        return view('admin/users/index', ['users' => $users]);
    }

    public function usersCreate() {
        return view('admin/users/create');
    }

    public function usersStore() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        // Basic validation
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['role'])) {
            // Handle error, maybe with session flash messages
            die('All fields are required.');
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            die('Invalid email format.');
        }

        $db = Database::getInstance()->getConnection();

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        if ($stmt->fetch()) {
            die('Email already in use.');
        }

        // Hash password
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Insert into database
        $stmt = $db->prepare(
            "INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)"
        );
        $stmt->execute([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password_hash' => $password_hash,
            'role' => $_POST['role']
        ]);

        header('Location: /admin/users');
        exit();
    }

    public function settings() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        return view('admin/settings', ['settings' => $settings]);
    }

    public function updateSettings() {
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) die('CSRF token validation failed.');

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");

        foreach ($_POST['settings'] as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        // Add a flash message for success
        header('Location: /admin/settings');
        exit();
    }

    public function gameLogs() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC");
        $logs = $stmt->fetchAll();

        return view('admin/logs', ['logs' => $logs]);
    }
}
