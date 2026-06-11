<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use App\Models\User;

class AuthMiddleware {
    public function __invoke(Request $request, Handler $handler): Response {
        // Obtener el token del Header 'Authorization'
        $token = $request->getHeaderLine('Authorization'); [cite: 101]

        if (empty($token)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token no proporcionado o inexistente'])); [cite: 101]
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401); [cite: 103]
        }

        // Validar la existencia y estado activo del token en la base de datos
        // Como no hay intercomunicación entre servicios, cada MS leerá de su BD o de la BD correspondiente.
        $user = User::where('token', $token)
                    ->where('session_active', 1)
                    ->first(); [cite: 102, 224]

        if (!$user) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado. Sesión inválida o expirada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401); [cite: 103]
        }

        // Si es válido, continúa con la petición
        return $handler->handle($request);
    }
}