<?php
use Slim\App;
use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Rutas protegidas para el catálogo del menú
    $app->group('/productos', function ($group) {
        $group->post('', [ProductoController::class, 'create']);
        $group->put('/{id}', [ProductoController::class, 'update']);
        $group->delete('/{id}', [ProductoController::class, 'delete']);
        $group->get('', [ProductoController::class, 'index']); // Permite listar y buscar por filtros (categoría/disponibilidad)
    })->add(new AuthMiddleware());
};