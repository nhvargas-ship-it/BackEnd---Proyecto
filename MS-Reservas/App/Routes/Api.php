<?php
use Slim\App;
use App\Controllers\ReservaController;
use App\Middleware\AuthMiddleware; // Reutilizas la lógica del middleware de verificación de sesión [cite: 100, 225]

return function (App $app) {
    // Rutas protegidas para Mesas [cite: 109, 110]
    $app->post('/mesas', [ReservaController::class, 'crearMesa'])->add(new AuthMiddleware());
    $app->get('/mesas', [ReservaController::class, 'listarMesas'])->add(new AuthMiddleware());

    // Rutas protegidas para Reservas [cite: 133, 135]
    $app->post('/reservas', [ReservaController::class, 'crearReserva'])->add(new AuthMiddleware());
    $app->get('/reservas', [ReservaController::class, 'consultarReservas'])->add(new AuthMiddleware());
};