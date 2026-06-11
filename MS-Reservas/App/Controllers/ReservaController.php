<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Mesa;
use App\Models\Reserva;

class ReservaController {
    
    // 8.2.1 GESTIÓN DE MESAS
    public function crearMesa(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $capacidad = (int)($data['capacidad'] ?? 0);

        // Validaciones obligatorias [cite: 130]
        if ($capacidad <= 0) { // La capacidad debe ser mayor a cero [cite: 132]
            $response->getBody()->write(json_encode(['error' => 'La capacidad debe ser mayor a cero']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (Mesa::where('numero_mesa', $data['numero_mesa'] ?? '')->exists()) { // No permitir mesas duplicadas [cite: 131]
            $response->getBody()->write(json_encode(['error' => 'La mesa ya existe']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $mesa = Mesa::create([
            'numero_mesa' => $data['numero_mesa'],
            'capacidad' => $capacidad,
            'estado' => $data['estado'] ?? 'Disponible' [cite: 115, 126]
        ]);

        $response->getBody()->write(json_encode($mesa));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function listarMesas(Request $request, Response $response) {
        $mesas = Mesa::all(); // Consultar mesas [cite: 119, 121]
        $response->getBody()->write(json_encode($mesas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 8.2.2 GESTIÓN DE RESERVAS
    public function crearReserva(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $fechaReserva = $data['fecha'] ?? '';
        $horaReserva = $data['hora'] ?? '';
        $mesaId = $data['mesa_id'] ?? '';
        $cantPersonas = (int)($data['cantidad_personas'] ?? 0);

        // Validaciones obligatorias [cite: 165]
        if (strtotime($fechaReserva) < strtotime(date('Y-m-d'))) { // No permitir reservas en fechas pasadas [cite: 166]
            $response->getBody()->write(json_encode(['error' => 'No se permiten reservas en fechas pasadas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $mesa = Mesa::find($mesaId);
        if (!$mesa || $mesa->estado === 'Fuera de servicio') { // No permitir reservar mesas fuera de servicio [cite: 167]
            $response->getBody()->write(json_encode(['error' => 'La mesa no está disponible o está fuera de servicio']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if ($cantPersonas > $mesa->capacidad) { // Validar capacidad máxima de la mesa [cite: 168]
            $response->getBody()->write(json_encode(['error' => 'La cantidad de personas excede la capacidad de la mesa']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // No permitir doble reserva para la misma mesa y horario [cite: 169]
        $dobleReserva = Reserva::where('mesa_id', $mesaId)
                               ->where('fecha', $fechaReserva)
                               ->where('hora', $horaReserva)
                               ->where('estado', '!=', 'Cancelada') // Exceptuando canceladas [cite: 163]
                               ->exists();
        if ($dobleReserva) {
            $response->getBody()->write(json_encode(['error' => 'La mesa ya está reservada para esa fecha y hora']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $reserva = Reserva::create([
            'nombre_cliente' => $data['nombre_cliente'],
            'telefono' => $data['telefono'],
            'cantidad_personas' => $cantPersonas,
            'fecha' => $fechaReserva,
            'hora' => $horaReserva,
            'mesa_id' => $mesaId,
            'observaciones' => $data['observaciones'] ?? '',
            'estado' => 'Pendiente' [cite: 161]
        ]);

        $response->getBody()->write(json_encode($reserva));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function consultarReservas(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $query = Reserva::query();

        // Buscar por filtros [cite: 146, 148, 149, 150]
        if (!empty($params['fecha'])) { $query->where('fecha', $params['fecha']); }
        if (!empty($params['cliente'])) { $query->where('nombre_cliente', 'LIKE', '%'.$params['cliente'].'%'); }
        if (!empty($params['estado'])) { $query->where('estado', $params['estado']); }

        $response->getBody()->write(json_encode($query->get()));
        return $response->withHeader('Content-Type', 'application/json');
    }
}