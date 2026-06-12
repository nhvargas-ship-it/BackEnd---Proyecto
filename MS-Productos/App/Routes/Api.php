<?php
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    // Grupo protegido para el menú
    $app->group('/productos', function (RouteCollectorProxy $group) {
        
        // GET http://127.0.0.1:8003/productos (Soporta ?categoria=Bebidas en el Frontend)
        $group->get('', [ProductoController::class, 'index']); 
        
        // POST http://127.0.0.1:8003/productos
        $group->post('', [ProductoController::class, 'create']); 
        
        // PUT http://127.0.0.1:8003/productos/{id}
        $group->put('/{id}', [ProductoController::class, 'update']); 
        
        // DELETE http://127.0.0.1:8003/productos/{id}
        $group->delete('/{id}', [ProductoController::class, 'delete']); 
        
    })->add(new AuthMiddleware());
};