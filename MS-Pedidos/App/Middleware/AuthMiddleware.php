<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use App\Models\User;

class AuthMiddleware {

    public function __invoke(
        Request $request,
        Handler $handler
    ): Response {


        $token = str_replace(
            'Bearer ',
            '',
            $request->getHeaderLine('Authorization')
        );


        if (empty($token)) {

            $response = new Response();

            $response->getBody()->write(
                json_encode([
                    'error' => 'Token no proporcionado'
                ])
            );

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json'
                )
                ->withStatus(401);
        }


        $user = User::where('token',$token)
                    ->where('session_active',1)
                    ->first();


        if (!$user) {

            $response = new Response();

            $response->getBody()->write(
                json_encode([
                    'error'=>'Sesión inválida o expirada'
                ])
            );

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json'
                )
                ->withStatus(401);
        }


        return $handler->handle($request);
    }
}