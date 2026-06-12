<?php
use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {
    // POST http://127.0.0.1:8001/login
    $app->post('/login', [AuthController::class, 'login']);
    
    // POST http://127.0.0.1:8001/logout
    $app->post('/logout', [AuthController::class, 'logout']);
};