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
    'database'  => $_ENV['DB_NAME'] ?? 'ms-pedidos',
    'username'  => $_ENV['DB_USER'] ?? 'root',
    'password'  => $_ENV['DB_PASS'] ?? '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Middleware de CORS Completo
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

// Modelo Eloquent con Timestamps desactivados para evitar rechazos de SQL
if (!class_exists('Pedido')) {
    class Pedido extends \Illuminate\Database\Eloquent\Model {
        protected $table = 'pedidos';
        protected $fillable = ['mesa_id', 'productos', 'total', 'estado'];
        public $timestamps = false; 
    }
}

$app->group('/api/pedidos', function ($group) {
    
    // GET: Listar pedidos
    $group->get('', function (Request $request, Response $response) {
        $pedidos = Pedido::orderBy('id', 'desc')->get();
        foreach ($pedidos as $p) {
            $p->productos = json_decode($p->productos);
        }
        $response->getBody()->write(json_encode($pedidos));
        return $response->withStatus(200);
    });
    
    // POST: Guardar comanda de comida
    $group->post('', function (Request $request, Response $response) {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $mesaId = $data['mesa_id'] ?? null;
        $productos = $data['productos'] ?? []; 
        $total = $data['total'] ?? 0;

        $pedido = Pedido::create([
            'mesa_id'   => $mesaId,
            'productos' => json_encode($productos), 
            'total'     => $total,
            'estado'    => 'pendiente'
        ]);

        $pedido->productos = $productos; 

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => '¡Comanda guardada exitosamente!',
            'pedido' => $pedido
        ]));
        return $response->withStatus(201);
    });

    // PUT: Actualizar estado de atención de la mesa
    $group->put('/{id}/estado', function (Request $request, Response $response, $args) {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $nuevoEstado = $data['estado'] ?? 'preparando';

        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->estado = $nuevoEstado;
            $pedido->save();
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => "El estado del pedido #$id cambió a '$nuevoEstado'"
            ]));
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Pedido no encontrado.'
            ]));
        }
        return $response->withStatus(200);
    });
});

$app->run();