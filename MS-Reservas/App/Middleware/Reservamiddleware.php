<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Illuminate\Database\Capsule\Manager as DB;

class AuthMiddleware {
    public function __invoke(Request $request, Handler $handler): Response {
        $token = $request->getHeaderLine('Authorization');

        if (empty($token)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token no proporcionado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Al no haber comunicación entre MS, se consulta a la tabla/BD que tenga los usuarios compartidos o centralizados en este entorno.
        $session = DB::table('usuarios')->where('token', $token)->where('session_active', 1)->first();

        if (!$session) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado. Sesión inválida']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        return $handler->handle($request);
    }
}