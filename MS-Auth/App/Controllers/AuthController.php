<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;

class AuthController
{
    // 8.1.1 Inicio de Sesión
    public function login(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $identificador = $data['identificador'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($identificador) || empty($password)) {
            $response->getBody()->write(json_encode(['error' => 'El identificador y la contraseña son obligatorios']));
            return $response->withStatus(400);
        }

        // Buscamos usando tus columnas reales: usuario o correo
        $user = User::where('usuario', $identificador)
                    ->orWhere('correo', $identificador)
                    ->first();

        // Validamos usando la columna contrasena
        if (!$user || $user->contrasena !== $password) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales incorrectas']));
            return $response->withStatus(401);
        }

        $tokenSimple = bin2hex(random_bytes(20));
        
        $user->token = $tokenSimple;
        $user->sesion_activa = 1; // Columna sesion_activa
        $user->save();

        $responseData = [
            'message' => 'Login exitoso',
            'token' => $tokenSimple,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'usuario' => $user->usuario,
                'rol' => $user->rol
            ]
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withStatus(200);
    }

    // 8.1.2 Cierre de Sesión
    public function logout(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $token = $data['token'] ?? '';

        if (empty($token)) {
            $response->getBody()->write(json_encode(['error' => 'El token es requerido para cerrar sesion']));
            return $response->withStatus(400);
        }

        $user = User::where('token', $token)->first();

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Token invalido o sesion no encontrada']));
            return $response->withStatus(404);
        }

        $user->token = null;
        $user->sesion_activa = 0;
        $user->save();

        $response->getBody()->write(json_encode(['message' => 'Sesion cerrada correctamente']));
        return $response->withStatus(200);
    }

    // 8.1.3 Validación de Sesión
    public function validateToken(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $token = $data['token'] ?? '';

        if (empty($token)) {
            $response->getBody()->write(json_encode(['valid' => false, 'message' => 'Token no proporcionado']));
            return $response->withStatus(400);
        }

        $user = User::where('token', $token)
                    ->where('sesion_activa', 1)
                    ->first();

        if (!$user) {
            $response->getBody()->write(json_encode([
                'valid' => false, 
                'message' => 'Usuario no autenticado o sesion expirada'
            ]));
            return $response->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'valid' => true, 
            'message' => 'Sesion activa',
            'user' => [
                'id' => $user->id,
                'usuario' => $user->usuario,
                'rol' => $user->rol
            ]
        ]));
        return $response->withStatus(200);
    }
}