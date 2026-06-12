<?php
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

// 1. Cargar el Autoload de Composer y las variables de entorno
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 2. Configurar la conexión a la Base de Datos con Eloquent ORM
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database'  => $_ENV['DB_DATABASE'] ?? 'restaurante_auth',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 3. Crear la instancia de la aplicación Slim
$app = AppFactory::create();

// 4. Middlewares globales esenciales (Manejo de CORS para conectar con el Frontend)
$app->addBodyParsingMiddleware();

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*') // Permite peticiones desde tu frontend
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Manejo integrado de errores en formato JSON
$app->addErrorMiddleware(true, true, true);

// 5. Cargar e inyectar los Endpoints de este microservicio
$routes = require __DIR__ . '/../app/Routes/api.php';
$routes($app);

// 6. Ejecutar el servidor de Slim
$app->run();