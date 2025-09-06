<?php

// Helper functions

/**
 * Render a view file.
 *
 * @param string $path The path to the view file.
 * @param array $data The data to be extracted for the view.
 */
function view($path, $data = []) {
    extract($data);
    return require __DIR__ . "/../../views/{$path}.view.php";
}

/**
 * Generate a CSRF token and store it in the session.
 *
 * @return string The generated token.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get the CSRF token from the session.
 *
 * @return string The CSRF token.
 */
function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Validate a CSRF token.
 *
 * @param string $token The token from the form submission.
 * @return bool True if the token is valid, false otherwise.
 */
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token)) {
        // For Single Page Applications or heavy AJAX pages, we use a session-long token.
        // Unsetting the token after first use would break subsequent AJAX calls on the same page.
        // The token is still secure as it's tied to the user's session.
        return true;
    }
    return false;
}
