<?php
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

// 1. Cargar variables de entorno (.env)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// 2. Inicializar Eloquent ORM
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database'  => $_ENV['DB_NAME'] ?? '',
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 3. Crear App de Slim
$app = AppFactory::create();

// Middleware para parsear JSON en el Body de las peticiones
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Manejo de Errores nativo de Slim
$app->addErrorMiddleware(true, true, true);

// 4. Cargar Rutas
$routes = require __DIR__ . '/../app/Routes/api.php';
$routes($app);

// Ejecutar aplicación
$app->run();