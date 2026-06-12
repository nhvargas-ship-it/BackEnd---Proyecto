<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$app = AppFactory::create();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database'  => $_ENV['DB_NAME'] ?? 'ms-reservas',
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// CORS Middleware
$app->options('/{routes:.+}', function ($request, $response, $args) { return $response; });
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Content-Type', 'application/json');
});

$app->addErrorMiddleware(true, true, true);

if (!class_exists('Mesa')) {
    class Mesa extends \Illuminate\Database\Eloquent\Model {
        protected $table = 'mesas';
        protected $fillable = ['numero', 'capacidad', 'estado'];
    }
}
if (!class_exists('Reserva')) {
    class Reserva extends \Illuminate\Database\Eloquent\Model {
        protected $table = 'reservas';
        protected $fillable = ['nombre_cliente', 'telefono_cliente', 'cantidad_personas', 'fecha', 'hora', 'estado', 'mesa_id'];
    }
}

$app->group('/api', function ($group) {
    
    $group->get('/mesas', function (Request $request, Response $response) {
        $mesas = Mesa::all();
        $response->getBody()->write(json_encode($mesas));
        return $response->withStatus(200);
    });

    $group->get('/reservas', function (Request $request, Response $response) {
        $reservas = Reserva::all();
        $response->getBody()->write(json_encode($reservas));
        return $response->withStatus(200);
    });

    // POST: Registro de reserva con validación inteligente anti-duplicados y cambio de estado de mesa
    $group->post('/reservas', function (Request $request, Response $response) {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $mesaId = $data['mesa_id'] ?? null;
        $fecha = $data['fecha'] ?? date('Y-m-d');
        $hora = $data['hora'] ?? date('H:i:s');

        // REGLA DE NEGOCIO: Validar si esa mesa ya está reservada en ese día y hora exactos
        $existeCita = Reserva::where('mesa_id', $mesaId)
                             ->where('fecha', $fecha)
                             ->where('hora', $hora)
                             ->first();

        if ($existeCita) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Lo sentimos, esta mesa ya tiene una reserva agendada para la misma fecha y hora.'
            ]));
            return $response->withStatus(400);
        }

        // Crear la reserva
        $reserva = Reserva::create([
            'nombre_cliente'    => $data['nombre_cliente'] ?? '',
            'telefono_cliente'  => $data['telefono_cliente'] ?? '',
            'cantidad_personas' => $data['cantidad_personas'] ?? 1,
            'fecha'             => $fecha,
            'hora'              => $hora,
            'mesa_id'           => $mesaId,
            'estado'            => 'confirmada'
        ]);

        // REGLA DE NEGOCIO: Modificar automáticamente el estado de la mesa asignada a "reservada"
        $mesa = Mesa::find($mesaId);
        if ($mesa) {
            $mesa->estado = 'reservada';
            $mesa->save();
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => '¡Reserva agendada con éxito!',
            'reserva' => $reserva
        ]));
        return $response->withStatus(201);
    });
});

$app->run();