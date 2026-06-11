<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Database\Capsule\Manager as DB;

class PedidoController {

    // 1. Crear pedido (Validando mesa ocupada/reservada y calculando totales)
    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $mesaId = $data['mesa_id'] ?? null;
        $items = $data['items'] ?? []; // Array de productos: [['producto_id' => 1, 'cantidad' => 2], ...]

        // Validación: No permitir pedidos vacíos
        if (empty($mesaId) || empty($items)) {
            $response->getBody()->write(json_encode(['error' => 'No se permiten pedidos vacíos o sin mesa asignada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación: No permitir registrar pedidos para mesas "disponibles"
        // (El pedido debe realizarse sobre una mesa que ya cambió su estado a ocupada o reservada)
        $mesa = DB::table('mesas')->where('id', $mesaId)->first();
        if (!$mesa || $mesa->estado === 'Disponible') {
            $response->getBody()->write(json_encode(['error' => 'No se pueden registrar pedidos en mesas disponibles o inexistentes']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Procesar y validar ítems dentro de una transacción de Base de Datos para asegurar la consistencia
        try {
            $resultado = DB::transaction(function() use ($mesaId, $items) {
                $subtotalPedido = 0;
                $cantidadTotalProductos = 0;
                $detallesAInsertar = [];

                foreach ($items as $item) {
                    $productoId = $item['producto_id'] ?? null;
                    $cantidad = (int)($item['cantidad'] ?? 0);

                    // Validación: No permitir cantidades menores a uno
                    if ($cantidad < 1) {
                        throw new \Exception("No se permiten cantidades menores a uno en los productos.");
                    }

                    // Consultar información del producto (Precio e id)
                    $producto = DB::table('productos')->where('id', $productoId)->first();
                    if (!$producto || !$producto->disponibilidad) {
                        throw new \Exception("El producto con ID {$productoId} no existe o no está disponible.");
                    }

                    $subtotalItem = $producto->precio * $cantidad;
                    $subtotalPedido += $subtotalItem;
                    $cantidadTotalProductos += $cantidad;

                    $detallesAInsertar[] = [
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $subtotalItem
                    ];
                }

                // Cálculos automáticos de la cabecera del pedido
                $pedido = Pedido::create([
                    'mesa_id' => $mesaId,
                    'estado' => 'Pendiente', // Estado inicial por defecto
                    'subtotal' => $subtotalPedido,
                    'total' => $subtotalPedido, // Asumiendo total igual al subtotal si no hay impuestos declarados
                    'cantidad_total' => $cantidadTotalProductos
                ]);

                // Guardar el desglose de productos asociados
                foreach ($detallesAInsertar as $detalle) {
                    $detalle['pedido_id'] = $pedido->id;
                    PedidoProducto::create($detalle);
                }

                return $pedido->load('items');
            });

            $response->getBody()->write(json_encode([
                'message' => 'Pedido registrado con éxito',
                'data' => $resultado
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(210);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    // 2. Consultar pedidos (Listar y filtrar por estado)
    public function index(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        $query = Pedido::with('items');

        // Filtrar por estado si viene en los parámetros de búsqueda (Pendiente, En preparación, etc.)
        if (!empty($queryParams['estado'])) {
            $query->where('estado', $queryParams['estado']);
        }

        $pedidos = $query->get();
        $response->getBody()->write($pedidos->toJson());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 3. Consultar detalle específico de un pedido
    public function show(Request $request, Response $response, array $args) {
        $pedido = Pedido::with('items')->find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write($pedido->toJson());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 4. Cambiar estado del pedido
    public function changeState(Request $request, Response $response, array $args) {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();
        $nuevoEstado = $data['estado'] ?? '';
        
        // Estados válidos según el requerimiento
        $estadosValidos = ['Pendiente', 'En preparación', 'Entregado', 'Pagado', 'Cancelado'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            $response->getBody()->write(json_encode(['error' => 'Estado de pedido no válido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pedido->update(['estado' => $nuevoEstado]);

        $response->getBody()->write(json_encode([
            'message' => 'Estado del pedido actualizado',
            'data' => $pedido
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 5. Agregar, modificar o eliminar productos del pedido (Actualización del carrito/detalle)
    public function updateItems(Request $request, Response $response, array $args) {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();
        $items = $data['items'] ?? []; // Nueva lista completa de productos solicitada

        if (empty($items)) {
            $response->getBody()->write(json_encode(['error' => 'No se puede actualizar el pedido dejándolo vacío']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            DB::transaction(function() use ($pedido, $items) {
                // Eliminar el detalle antiguo para reescribirlo con los nuevos cambios (Modificaciones/Eliminaciones)
                PedidoProducto::where('pedido_id', $pedido->id)->delete();

                $subtotalPedido = 0;
                $cantidadTotalProductos = 0;

                foreach ($items as $item) {
                    $productoId = $item['producto_id'] ?? null;
                    $cantidad = (int)($item['cantidad'] ?? 0);

                    if ($cantidad < 1) {
                        throw new \Exception("No se permiten cantidades menores a uno.");
                    }

                    $producto = DB::table('productos')->where('id', $productoId)->first();
                    if (!$producto) {
                        throw new \Exception("Producto inválido.");
                    }

                    $subtotalItem = $producto->precio * $cantidad;
                    $subtotalPedido += $subtotalItem;
                    $cantidadTotalProductos += $cantidad;

                    PedidoProducto::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $subtotalItem
                    ]);
                }

                // Recalcular automáticamente los totales del pedido principal
                $pedido->update([
                    'subtotal' => $subtotalPedido,
                    'total' => $subtotalPedido,
                    'cantidad_total' => $cantidadTotalProductos
                ]);
            ]);

            $response->getBody()->write(json_encode([
                'message' => 'Productos del pedido modificados con éxito',
                'data' => $pedido->load('items')
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}