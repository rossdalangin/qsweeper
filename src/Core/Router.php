<?php

namespace App\Core;

use Exception;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function load($file) {
        $router = new static;
        require $file;
        return $router;
    }

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch($uri, $requestType) {
        // Find a matching route
        foreach ($this->routes[$requestType] as $route => $controllerAction) {
            // Convert route to regex: /users/{id} -> /users/(\w+)
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route);
            $pattern = "@^" . $pattern . "$@";

            if (preg_match($pattern, $uri, $matches)) {
                // Get controller and action
                [$controller, $action] = explode('@', $controllerAction);

                // Get named capture groups
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->callAction($controller, $action, $params);
            }
        }

        throw new Exception('No route defined for this URI.');
    }

    protected function callAction($controller, $action, $params = []) {
        $controller = "App\\Controllers\\{$controller}";
        $controller = new $controller;

        if (!method_exists($controller, $action)) {
            throw new Exception(
                "{$controller} does not respond to the {$action} action."
            );
        }

        return $controller->$action(...array_values($params));
    }
}
