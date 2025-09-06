<?php

namespace App\Core;

use Exception;

/**
 * Router Class
 * Handles routing of requests to the appropriate controller and action.
 */
class Router {
    /** @var array The registered routes. */
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * Loads a routes file and returns a new Router instance.
     *
     * @param string $file The path to the routes file.
     * @return Router A new Router instance.
     */
    public static function load($file) {
        $router = new static;
        require $file;
        return $router;
    }

    /**
     * Registers a GET route.
     *
     * @param string $uri The route URI.
     * @param string $controller The 'Controller@method' string.
     */
    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * Registers a POST route.
     *
     * @param string $uri The route URI.
     * @param string $controller The 'Controller@method' string.
     */
    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    /**
     * Dispatches the request to the appropriate controller action.
     *
     * @param string $uri The request URI.
     * @param string $requestType The request method ('GET' or 'POST').
     * @return mixed The result of the controller action.
     * @throws Exception If no route is defined for the URI.
     */
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

    /**
     * Calls a controller action with the given parameters.
     *
     * @param string $controller The name of the controller class.
     * @param string $action The name of the method to call.
     * @param array $params The parameters to pass to the method.
     * @return mixed The result of the controller action.
     * @throws Exception If the controller method does not exist.
     */
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
