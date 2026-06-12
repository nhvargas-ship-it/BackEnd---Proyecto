<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Mesa;

class MesaController
{
    public function getAll(Request $request, Response $response): Response
    {
        $mesas = Mesa::all();
        $response->getBody()->write(json_encode($mesas));
        return $response->withStatus(200);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $mesa = Mesa::create($data);
        $response->getBody()->write(json_encode(['message' => 'Mesa guardada', 'mesa' => $mesa]));
        return $response->withStatus(201);
    }

    public function updateEstado(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        
        $mesa = Mesa::find($id);
        if (!$mesa) {
            $response->getBody()->write(json_encode(['error' => 'Mesa no encontrada']));
            return $response->withStatus(404);
        }

        $mesa->estado = $data['estado'] ?? $mesa->estado;
        $mesa->save();

        $response->getBody()->write(json_encode(['message' => 'Estado de mesa modificado', 'mesa' => $mesa]));
        return $response->withStatus(200);
    }
}