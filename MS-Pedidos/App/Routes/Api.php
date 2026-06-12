<?php
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\PedidoController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Grupo protegido para la facturación y comandas
    $app->group('/pedidos', function (RouteCollectorProxy $group) {
        
        // GET http://127.0.0.1:8004/pedidos
        $group->get('', [PedidoController::class, 'index']); 
        
        // POST http://127.0.0.1:8004/pedidos (Retorna el código de éxito 210)
        $group->post('', [PedidoController::class, 'create']); 
        
        // GET http://127.0.0.1:8004/pedidos/{id}
        $group->get('/{id}', [PedidoController::class, 'show']); 
        
        // PATCH http://127.0.0.1:8004/pedidos/{id}/estado (Para cocina o caja)
        $group->patch('/{id}/estado', [PedidoController::class, 'changeState']); 
        
        // PUT http://127.0.0.1:8004/pedidos/{id}/items (Modificar cantidades de la comanda)
        $group->put('/{id}/items', [PedidoController::class, 'updateItems']); 
        
    })->add(new AuthMiddleware());
};