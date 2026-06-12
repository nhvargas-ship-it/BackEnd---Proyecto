<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Producto;

class ProductoController
{
    // Obtener menú trayendo también el nombre de su categoría
    public function getAll(Request $request, Response $response): Response
    {
        $productos = Producto::with('categoria')->get();
        $response->getBody()->write(json_encode($productos));
        return $response->withStatus(200);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        if (empty($data['nombre']) || empty($data['precio']) || empty($data['categoria_id'])) {
            $response->getBody()->write(json_encode(['error' => 'Campos obligatorios incompletos']));
            return $response->withStatus(400);
        }

        $producto = Producto::create($data);
        
        $response->getBody()->write(json_encode([
            'message' => 'Producto creado con éxito',
            'producto' => $producto
        ]));
        return $response->withStatus(201);
    }
}