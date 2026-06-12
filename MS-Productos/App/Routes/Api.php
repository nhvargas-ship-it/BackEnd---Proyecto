<<?php
use App\Controllers\ProductoController;
use App\Middleware\JsonResponseMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    // Grupo de rutas para productos
    $app->group('/api/productos', function (RouteCollectorProxy $group) {
        // GET http://localhost:8001/api/productos
        $group->get('', [ProductoController::class, 'getAll']);
        
        // POST http://localhost:8001/api/productos
        $group->post('', [ProductoController::class, 'create']);
    })->add(new JsonResponseMiddleware());
};