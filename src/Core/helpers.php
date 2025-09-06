<?php

// Helper functions

/**
 * Render a view file.
 *
 * @param string $path The path to the view file (e.g., 'users/index').
 * @param array $data The data to be extracted and made available to the view.
 * @return mixed
 */
function view($path, $data = []) {
    extract($data);
    return require __DIR__ . "/../../views/{$path}.view.php";
}

/**
 * Generate and retrieve a CSRF token, storing it in the session if it doesn't exist.
 *
 * @return string The generated CSRF token.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the one stored in the session.
 * This uses a session-bound token that is not invalidated after one use,
 * making it suitable for pages with multiple AJAX requests.
 *
 * @param string $token The token from the form submission or request header.
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
