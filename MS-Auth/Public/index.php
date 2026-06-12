<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Inicializar base de datos
require __DIR__ . '/../app/Config/database.php';

// Crear aplicación Slim
$app = AppFactory::create();

// Middleware nativo para procesar Body (JSON, form data)
$app->addBodyParsingMiddleware();

// Middleware de enrutamiento
$app->addRoutingMiddleware();

// Middleware de errores (mostrar detalles de error en desarrollo)
$app->addErrorMiddleware(true, true, true);

// Cargar rutas
$routes = require __DIR__ . '/../app/Routes/api.php';
$routes($app);

$app->run();