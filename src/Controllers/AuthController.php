<?php
/**
 * Auth Controller - Admin authentication
 */

declare(strict_types=1);

namespace ProgressiveBar\Controllers;

use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Models\Admin;
use ProgressiveBar\Middleware\AuthMiddleware;

class AuthController
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): void
    {
        $email = $request->getBody('email');
        $password = $request->getBody('password');
        
        if (!$email || !$password) {
            Response::error('Email and password required', 400);
            return;
        }
        
        $admin = Admin::findByEmail($email);
        
        if (!$admin || !Admin::verifyPassword($password, $admin['password_hash'])) {
            Response::unauthorized('Invalid credentials');
            return;
        }
        
        // Generate JWT token
        $token = AuthMiddleware::generateToken([
            'id' => (string) $admin['id'],
            'email' => $admin['email'],
            'role' => $admin['role'] ?? 'admin',
        ]);
        
        Response::success([
            'token' => $token,
            'user' => [
                'id' => (string) $admin['id'],
                'email' => $admin['email'],
                'name' => $admin['name'] ?? null,
                'role' => $admin['role'] ?? 'admin',
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): void
    {
        Response::success(['message' => 'Logged out successfully']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): void
    {
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            Response::unauthorized();
            return;
        }
        
        $admin = Admin::findById($userId);
        
        if (!$admin) {
            Response::notFound('User not found');
            return;
        }
        
        Response::success([
            'id' => (string) $admin['id'],
            'email' => $admin['email'],
            'name' => $admin['name'] ?? null,
            'role' => $admin['role'] ?? 'admin',
        ]);
    }
}
