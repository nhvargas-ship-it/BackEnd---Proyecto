<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Reserva;

class ReservaController
{
    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        if (empty($data['mesa_id']) || empty($data['fecha']) || empty($data['hora'])) {
            $response->getBody()->write(json_encode(['error' => 'Datos de reserva incompletos']));
            return $response->withStatus(400);
        }

        $reserva = Reserva::create($data);
        $response->getBody()->write(json_encode(['message' => 'Reserva creada con éxito', 'reserva' => $reserva]));
        return $response->withStatus(201);
    }

    public function getAll(Request $request, Response $response): Response
    {
        $reservas = Reserva::all();
        $response->getBody()->write(json_encode($reservas));
        return $response->withStatus(200);
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $reserva = Reserva::find($id);
        
        if (!$reserva) {
            $response->getBody()->write(json_encode(['error' => 'Reserva no encontrada']));
            return $response->withStatus(404);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $response->getBody()->write(json_encode(['message' => 'La reserva ha sido cancelada']));
        return $response->withStatus(200);
    }
}