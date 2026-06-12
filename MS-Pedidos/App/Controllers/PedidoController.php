<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Database\Capsule\Manager as DB;

class PedidoController
{
    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);

        if (empty($data['mesa_id']) || empty($data['productos'])) {
            $response->getBody()->write(json_encode(['error' => 'Mesa o lista de productos vacía']));
            return $response->withStatus(400);
        }

        try {
            // Iniciamos transacción para asegurar que se guarde el pedido Y sus detalles juntos
            DB::beginTransaction();

            $pedido = Pedido::create([
                'mesa_id' => $data['mesa_id'],
                'cliente' => $data['cliente'] ?? 'Mesa ' . $data['mesa_id'],
                'total' => $data['total'],
                'estado' => 'pendiente'
            ]);

            foreach ($data['productos'] as $prod) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $prod['producto_id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio_unitario']
                ]);
            }

            DB::commit();

            $response->getBody()->write(json_encode([
                'message' => 'Pedido y detalles registrados correctamente',
                'pedido_id' => $pedido->id
            ]));
            return $response->withStatus(201);

        } catch (\Exception $e) {
            DB::rollBack();
            $response->getBody()->write(json_encode(['error' => 'Error al procesar pedido: ' . $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function updateEstado(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        
        $pedido = Pedido::find($id);
        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no existente']));
            return $response->withStatus(404);
        }

        $pedido->estado = $data['estado'] ?? $pedido->estado;
        $pedido->save();

        $response->getBody()->write(json_encode(['message' => 'Estado del pedido actualizado', 'pedido' => $pedido]));
        return $response->withStatus(200);
    }
}