<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;

class AuthController {
    
    // 8.1.1 Inicio de sesión
    public function login(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $usernameOrEmail = $data['usuario'] ?? '';
        $password = $data['password'] ?? '';

        // Validar campos vacíos
        if (empty($usernameOrEmail) || empty($password)) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales incompletas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Buscar usuario por nombre de usuario o correo
        $user = User::where('usuario', $usernameOrEmail)
                    ->orWhere('correo', $usernameOrEmail)
                    ->first();

        // Validar credenciales (asumiendo que están en texto plano o md5/bcrypt según defina tu BD entregada)
        if (!$user || $user->password !== $password) { [cite: 86, 92]
            $response->getBody()->write(json_encode(['error' => 'Usuario o contraseña incorrectos'])); [cite: 92]
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Generar un token simple
        $token = bin2hex(random_bytes(16)); [cite: 88, 223]

        // Actualizar estado de sesión en la base de datos
        $user->update([
            'token' => $token,
            'logged' => 1,
            'session_active' => 1
        ]); [cite: 90, 224]

        // Retornar información al frontend
        $payload = json_encode([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'usuario' => $user->usuario
            ]
        ]); [cite: 91]

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // 8.1.2 Cierre de sesión
    public function logout(Request $request, Response $response) {
        // El token viene validado previamente por el Middleware y se puede rescatar de los headers
        $token = $request->getHeaderLine('Authorization'); [cite: 96]

        $user = User::where('token', $token)->first();
        if ($user) {
            // Invalida el token y limpia el estado
            $user->update([
                'token' => null,
                'logged' => 0,
                'session_active' => 0
            ]); [cite: 97]
        }

        $response->getBody()->write(json_encode(['message' => 'Sesión cerrada correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}