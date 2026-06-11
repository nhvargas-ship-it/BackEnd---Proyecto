<?php
use Slim\App;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Rutas Públicas
    $app->post('/login', [AuthController::class, 'login']); [cite: 80]

    // Rutas Protegidas (Ejemplo usando el Middleware de validación)
    $app->post('/logout', [AuthController::class, 'logout'])->add(new AuthMiddleware()); [cite: 93]
};