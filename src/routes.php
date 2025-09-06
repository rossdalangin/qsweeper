<?php

// Define application routes

// $router->get(URI, Controller@method)

// Public routes
$router->get('', 'PagesController@home');
$router->get('about', 'PagesController@about');
$router->get('instructions', 'PagesController@instructions');

// Authentication routes
$router->get('login', 'AuthController@create');
$router->post('login', 'AuthController@store');
$router->post('logout', 'AuthController@destroy');

// Protected routes
$router->get('dashboard', 'DashboardController@index');

// Admin-specific routes
$router->get('admin', 'AdminController@index'); // Redirects to users list
$router->get('admin/users', 'AdminController@usersIndex');
$router->get('admin/users/create', 'AdminController@usersCreate');
$router->post('admin/users/create', 'AdminController@usersStore');
$router->get('admin/users/edit', 'AdminController@usersEdit');
$router->post('admin/users/edit', 'AdminController@usersUpdate');
$router->post('admin/users/delete', 'AdminController@usersDestroy');

$router->get('admin/settings', 'AdminController@settings');
$router->post('admin/settings', 'AdminController@updateSettings');

$router->get('admin/logs', 'AdminController@gameLogs');

// Teacher-specific routes
$router->get('teacher', 'TeacherController@index');
$router->get('teacher/groups', 'TeacherController@groupsIndex');
$router->get('teacher/groups/create', 'TeacherController@groupsCreate');
$router->post('teacher/groups/create', 'TeacherController@groupsStore');
$router->get('teacher/groups/edit', 'TeacherController@groupsEdit');
$router->post('teacher/groups/edit', 'TeacherController@groupsUpdate');
$router->post('teacher/groups/delete', 'TeacherController@groupsDestroy');
$router->get('teacher/groups/view', 'TeacherController@viewGroup');
$router->post('teacher/groups/add-member', 'TeacherController@addMember');
$router->post('teacher/groups/remove-member', 'TeacherController@removeMember');

$router->get('teacher/quizzes', 'TeacherController@quizzesIndex');
$router->get('teacher/quizzes/create', 'TeacherController@quizzesCreate');
$router->post('teacher/quizzes/create', 'TeacherController@quizzesStore');
$router->get('teacher/quizzes/edit', 'TeacherController@quizzesEdit');
$router->post('teacher/quizzes/edit', 'TeacherController@quizzesUpdate');
$router->post('teacher/quizzes/delete', 'TeacherController@quizzesDestroy');

// Question Management
$router->get('teacher/quizzes/{id}/questions', 'QuestionController@index');
$router->post('teacher/quizzes/{id}/questions', 'QuestionController@store');
$router->get('teacher/questions/{id}/edit', 'QuestionController@edit');
$router->post('teacher/questions/{id}/edit', 'QuestionController@update');
$router->post('teacher/questions/{id}/delete', 'QuestionController@destroy');

// Game Management
$router->get('games/create', 'GameController@create');
$router->post('games/create', 'GameController@store');
$router->get('games/{id}/lobby', 'GameController@lobby');
$router->post('games/{id}/start', 'GameController@startGame');
$router->post('games/{id}/cancel', 'GameController@cancelGame');
$router->get('games/{id}/board', 'GameController@board');
$router->post('games/{id}/end', 'GameController@endGame');
$router->get('games/{id}/results', 'GameController@results');
$router->get('exports/game/{id}/csv', 'GameController@exportCsv');

// API routes
$router->get('api/games/{id}/state', 'ApiController@getGameState');
$router->post('api/games/{id}/reveal', 'ApiController@revealTile');
$router->post('api/games/{id}/answer', 'ApiController@submitAnswer');
