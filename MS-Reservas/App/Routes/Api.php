<?php
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\MesaController;
use App\Controllers\ReservaController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Grupo protegido con el prefijo /api
    $app->group('/api', function (RouteCollectorProxy $group) {
        
        // --- Endpoints de Mesas ---
        // GET http://127.0.0.1:8002/api/mesas
        $group->get('/mesas', [MesaController::class, 'index']);
        
        // POST http://127.0.0.1:8002/api/mesas
        $group->post('/mesas', [MesaController::class, 'create']);
        
        // PUT http://127.0.0.1:8002/api/mesas/{id}
        $group->put('/mesas/{id}', [MesaController::class, 'update']);
        
        // PATCH http://127.0.0.1:8002/api/mesas/{id}/estado
        $group->patch('/mesas/{id}/estado', [MesaController::class, 'changeState']);

        // --- Endpoints de Reservas ---
        // GET http://127.0.0.1:8002/api/reservas
        $group->get('/reservas', [ReservaController::class, 'index']);
        
        // POST http://127.0.0.1:8002/api/reservas
        $group->post('/reservas', [ReservaController::class, 'create']);
        
        // PUT http://127.0.0.1:8002/api/reservas/{id}
        $group->put('/reservas/{id}', [ReservaController::class, 'update']);
        
        // DELETE http://127.0.0.1:8002/api/reservas/{id}
        $group->delete('/reservas/{id}', [ReservaController::class, 'cancel']);
        
    })->add(new AuthMiddleware()); // Restringe el acceso si no hay token válido
};