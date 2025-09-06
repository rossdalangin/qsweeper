<?php

namespace App\Core;

/**
 * Request Class
 * Provides helper methods for accessing the request URI and method.
 */
class Request {
    /**
     * Gets the clean request URI, stripped of the base application path.
     *
     * @return string The clean URI (e.g., 'users/create').
     */
    public static function uri() {
        // Get the full request URI path
        $requestUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Get the path from the app's base URL defined in config.php
        $baseUrlPath = parse_url(APP_URL, PHP_URL_PATH);

        // If the app is in a subdirectory, the base path might not end in a slash
        // e.g. /my-app/public. We ensure it's handled correctly.
        if ($baseUrlPath === null) {
            $baseUrlPath = '';
        }

        // Remove the base URL path from the start of the request URI path
        if (strpos($requestUriPath, $baseUrlPath) === 0) {
            $requestUriPath = substr($requestUriPath, strlen($baseUrlPath));
        }

        // Trim leading/trailing slashes to get the clean route
        return trim($requestUriPath, '/');
    }

    /**
     * Gets the request method (e.g., 'GET', 'POST').
     *
     * @return string The request method.
     */
    public static function method() {
        return $_SERVER['REQUEST_METHOD']; // e.g., GET, POST
    }
}
