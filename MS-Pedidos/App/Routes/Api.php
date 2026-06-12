<?php
use App\Controllers\PedidoController;
use App\Middleware\JsonResponseMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    // Grupo de rutas para pedidos
    $app->group('/api/pedidos', function (RouteCollectorProxy $group) {
        
        // POST http://localhost:8002/api/pedidos
        $group->post('', [PedidoController::class, 'create']);
        
        // PUT http://localhost:8002/api/pedidos/{id}/estado
        $group->put('/{id}/estado', [PedidoController::class, 'updateEstado']);
        
    })->add(new JsonResponseMiddleware());
};