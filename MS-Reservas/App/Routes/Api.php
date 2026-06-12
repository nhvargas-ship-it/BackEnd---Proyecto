<?php
use App\Controllers\ReservaController;
use App\Controllers\MesaController; // <- IMPORTANTE: Importar el nuevo controlador
use App\Middleware\JsonResponseMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    
    // --- GRUPO DE RESERVAS ---
    $app->group('/api/reservas', function (RouteCollectorProxy $group) {
        $group->post('', [ReservaController::class, 'create']);
        $group->get('', [ReservaController::class, 'getAll']);
        $group->put('/{id}/cancelar', [ReservaController::class, 'cancelar']);
    })->add(new JsonResponseMiddleware());


    // --- GRUPO DE MESAS ---
    $app->group('/api/mesas', function (RouteCollectorProxy $group) {
        $group->get('', [MesaController::class, 'getAll']);
        $group->post('', [MesaController::class, 'create']);
        $group->put('/{id}/estado', [MesaController::class, 'updateEstado']);
    })->add(new JsonResponseMiddleware());

};