<?php
/**
 * JWT Authentication Middleware
 */

declare(strict_types=1);

namespace ProgressiveBar\Middleware;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Core\App;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware
{
    private string $jwtSecret;

    public function __construct()
    {
        $app = App::getInstance();
        $this->jwtSecret = $app->config('jwt.secret', 'default-secret');
    }

    public function handle(Request $request): Request
    {
        // Skip auth for login endpoint
        if ($request->getUri() === '/api/admin/login' && $request->getMethod() === 'POST') {
            return $request;
        }

        $token = $request->getBearerToken();
        
        if (!$token) {
            Response::unauthorized('Authentication required');
            return $request->withAttribute('terminate', true);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Add user info to request
            return $request
                ->withAttribute('user_id', $decoded->sub)
                ->withAttribute('user_email', $decoded->email)
                ->withAttribute('user_role', $decoded->role);
                
        } catch (ExpiredException $e) {
            Response::unauthorized('Token has expired');
            return $request->withAttribute('terminate', true);
        } catch (\Exception $e) {
            Response::unauthorized('Invalid token');
            return $request->withAttribute('terminate', true);
        }
    }

    public static function generateToken(array $userData): string
    {
        $app = App::getInstance();
        $secret = $app->config('jwt.secret');
        $expiry = $app->config('jwt.expiry', 3600);
        
        $issuedAt = time();
        $expiresAt = $issuedAt + $expiry;
        
        $payload = [
            'iss' => 'progressive-bar',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'sub' => $userData['id'],
            'email' => $userData['email'],
            'role' => $userData['role'] ?? 'staff',
        ];
        
        return JWT::encode($payload, $secret, 'HS256');
    }
}
