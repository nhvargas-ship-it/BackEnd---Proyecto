<?php
use App\Controllers\AuthController;
use App\Middleware\JsonResponseMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    // Grupo de rutas para auth con el middleware JSON
    $app->group('/api/auth', function (RouteCollectorProxy $group) {
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/logout', [AuthController::class, 'logout']);
        $group->post('/validate', [AuthController::class, 'validateToken']);
    })->add(new JsonResponseMiddleware());
};