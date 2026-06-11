<?php
use Slim\App;
use App\Controllers\PedidoController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Rutas operacionales de comandas y pedidos
    $app->group('/pedidos', function ($group) {
        $group->post('', [PedidoController::class, 'create']);                    // Crear pedido
        $group->get('', [PedidoController::class, 'index']);                      // Listar y buscar por estado
        $group->get('/{id}', [PedidoController::class, 'show']);                  // Consultar detalle completo
        $group->put('/{id}/items', [PedidoController::class, 'updateItems']);      // Agregar/modificar/eliminar productos de un pedido
        $group->patch('/{id}/estado', [PedidoController::class, 'changeState']);   // Cambiar estado (Pendiente, En preparación, etc.)
    })->add(new AuthMiddleware());
};