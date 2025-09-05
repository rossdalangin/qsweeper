<?php

// Quiz Sweeper - Main Entry Point

require __DIR__ . '/../src/bootstrap.php';

use App\Core\Router;
use App\Core\Request;

// Load the router and dispatch the request
Router::load(__DIR__ . '/../src/routes.php')
    ->dispatch(Request::uri(), Request::method());
