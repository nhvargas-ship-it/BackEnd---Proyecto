<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use Slim\Factory\AppFactory;

// Inicializar base de datos
Database::initialize();

// Crear app Slim
$app = AppFactory::create();

// Middleware de errores
$app->addErrorMiddleware(true, true, true);

// Middleware para parsear JSON
$app->addBodyParsingMiddleware();

// Middleware CORS
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Manejar preflight OPTIONS
$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

// Cargar rutas
(require __DIR__ . '/../app/Routes/api.php')($app);

$app->run();