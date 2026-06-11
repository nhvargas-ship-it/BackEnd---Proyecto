<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Producto;

class ProductoController {
    
    // 1. Consultar productos (Listar, buscar por categoría y consultar disponibilidad)
    public function index(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        $query = Producto::query();

        // Filtrar por categoría si viene en la URL (ej: /productos?categoria=Postres)
        if (!empty($queryParams['categoria'])) {
            $query->where('categoria', $queryParams['categoria']);
        }

        // Filtrar por disponibilidad si viene en la URL (ej: /productos?disponibilidad=1)
        if (isset($queryParams['disponibilidad']) && $queryParams['disponibilidad'] !== '') {
            $query->where('disponibilidad', (int)$queryParams['disponibilidad']);
        }

        $productos = $query->get();

        $response->getBody()->write($productos->toJson());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 2. Crear producto (con validaciones obligatorias)
    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        
        $nombre = trim($data['nombre'] ?? '');
        $categoria = trim($data['categoria'] ?? '');
        $precio = isset($data['precio']) ? (float)$data['precio'] : 0;
        $disponibilidad = isset($data['disponibilidad']) ? (int)$data['disponibilidad'] : 1;

        // Validación: No permitir nombres vacíos
        if (empty($nombre)) {
            $response->getBody()->write(json_encode(['error' => 'El nombre del producto no puede estar vacío']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación: El precio debe ser mayor a cero
        if ($precio <= 0) {
            $response->getBody()->write(json_encode(['error' => 'El precio debe ser mayor a cero']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación: No permitir productos duplicados (mismo nombre)
        $existe = Producto::where('nombre', $nombre)->exists();
        if ($existe) {
            $response->getBody()->write(json_encode(['error' => 'Ya existe un producto con este nombre']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // Crear registro mediante Eloquent
        $producto = Producto::create([
            'nombre' => $nombre,
            'categoria' => $categoria,
            'precio' => $precio,
            'disponibilidad' => $disponibilidad
        ]);

        $response->getBody()->write(json_encode([
            'message' => 'Producto creado con éxito',
            'data' => $producto
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(210);
    }

    // 3. Editar producto
    public function update(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $producto = Producto::find($id);

        if (!$producto) {
            $response->getBody()->write(json_encode(['error' => 'Producto no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();
        
        // Mantener valores actuales si no se envían en el cuerpo de la petición
        $nombre = isset($data['nombre']) ? trim($data['nombre']) : $producto->nombre;
        $categoria = isset($data['categoria']) ? trim($data['categoria']) : $producto->categoria;
        $precio = isset($data['precio']) ? (float)$data['precio'] : $producto->precio;
        $disponibilidad = isset($data['disponibilidad']) ? (int)$data['disponibilidad'] : $producto->disponibilidad;

        // Validación: No permitir nombres vacíos al editar
        if (empty($nombre)) {
            $response->getBody()->write(json_encode(['error' => 'El nombre del producto no puede estar vacío']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación: El precio debe ser mayor a cero
        if ($precio <= 0) {
            $response->getBody()->write(json_encode(['error' => 'El precio debe ser mayor a cero']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación: No duplicados (exceptuando el ID del producto actual que se edita)
        $existe = Producto::where('nombre', $nombre)->where('id', '!=', $id)->exists();
        if ($existe) {
            $response->getBody()->write(json_encode(['error' => 'Ya existe otro producto con este nombre']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // Actualizar valores
        $producto->update([
            'nombre' => $nombre,
            'categoria' => $categoria,
            'precio' => $precio,
            'disponibilidad' => $disponibilidad
        ]);

        $response->getBody()->write(json_encode([
            'message' => 'Producto actualizado con éxito',
            'data' => $producto
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 4. Eliminar producto
    public function delete(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $producto = Producto::find($id);

        if (!$producto) {
            $response->getBody()->write(json_encode(['error' => 'Producto no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Eliminar registro
        $producto->delete();

        $response->getBody()->write(json_encode(['message' => 'Producto eliminado correctamente del menú']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}