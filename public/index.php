<?php
/**
 * Progressive Bar API - Single Entry Point (HTTP Service)
 * 
 * All requests route through this file for security and consistency.
 * Implements industry-standard security protocols.
 */

declare(strict_types=1);

// ============================================
// CORS HEADERS - MUST BE FIRST!
// ============================================
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Allow Vercel, localhost, and Koyeb domains
$allowedPatterns = [
    '/^https:\/\/[a-z0-9-]+\.vercel\.app$/',
    '/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/',
    '/^https:\/\/[a-z0-9-]+\.koyeb\.app$/',
];

$originAllowed = false;
foreach ($allowedPatterns as $pattern) {
    if (preg_match($pattern, $origin)) {
        $originAllowed = true;
        break;
    }
}

if ($originAllowed && $origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin");
    header("Access-Control-Max-Age: 86400");
    header("Vary: Origin");
}

// Handle preflight immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
// ============================================

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use ProgressiveBar\Core\App;
use ProgressiveBar\Core\Request;
use ProgressiveBar\Core\Response;
use ProgressiveBar\Core\Router;
use ProgressiveBar\Middleware\RateLimitMiddleware;
use ProgressiveBar\Middleware\SecurityMiddleware;
use ProgressiveBar\Middleware\AuthMiddleware;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Initialize application
$app = new App();

// Global exception handler
set_exception_handler(function (Throwable $e) {
    $response = new Response();
    
    $statusCode = 500;
    $message = 'Internal Server Error';
    
    if ($e instanceof \ProgressiveBar\Exceptions\HttpException) {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage();
    }
    
    // Log error details
    error_log(sprintf(
        "[%s] %s in %s:%d\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
    
    // Show error response
    $errorDetails = [
        'success' => false,
        'error' => $message,
        'code' => $statusCode
    ];
    
    $response->json($errorDetails, $statusCode);
});

// Create request from globals
$request = Request::createFromGlobals();

// Apply global middleware stack (CORS is handled at top of file)
$middlewareStack = [
    new SecurityMiddleware(),   // XSS, CSRF, injection protection
    new RateLimitMiddleware(), // Rate limiting
];

foreach ($middlewareStack as $middleware) {
    $request = $middleware->handle($request);
    
    // Check if middleware wants to terminate early
    if ($request->getAttribute('terminate')) {
        exit;
    }
}

// Initialize router
$router = new Router();

// ====================
// PUBLIC ROUTES
// ====================

// Health check
$router->get('/api/health', function (Request $req) {
    return Response::json(['status' => 'ok', 'timestamp' => time()]);
});

// Debug: Test database connection
$router->get('/api/debug/db', function (Request $req) {
    try {
        $pdo = \ProgressiveBar\Core\Database::getConnection();
        $result = $pdo->query("SELECT 1 as test")->fetch();
        return Response::json([
            'success' => true,
            'message' => 'Database connected',
            'test' => $result,
            'env' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'not set',
            'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'not set'
        ]);
    } catch (\Exception $e) {
        return Response::json([
            'success' => false,
            'error' => $e->getMessage(),
            'env' => $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'not set',
            'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'not set'
        ], 500);
    }
});

// Menu endpoints
$router->get('/api/menu', 'MenuController@index');
$router->get('/api/menu/{id}', 'MenuController@show');
$router->post('/api/menu/availability', 'MenuController@checkAvailability');

// Order endpoints
$router->post('/api/order', 'OrderController@create');
$router->get('/api/order/{id}', 'OrderController@show');
$router->get('/api/order/wait-time', 'OrderController@getWaitTime');

// ====================
// ADMIN ROUTES (Protected)
// ====================

$router->group(['middleware' => [AuthMiddleware::class]], function (Router $router) {
    // Authentication
    $router->post('/api/admin/login', 'AuthController@login');
    $router->post('/api/admin/logout', 'AuthController@logout');
    $router->get('/api/admin/me', 'AuthController@me');
    
    // Order management
    $router->get('/api/admin/orders', 'AdminOrderController@index');
    $router->get('/api/admin/orders/{id}', 'AdminOrderController@show');
    $router->patch('/api/admin/orders/{id}', 'AdminOrderController@update');
    $router->delete('/api/admin/orders/{id}', 'AdminOrderController@delete');
    
    // Menu management
    $router->post('/api/admin/menu', 'AdminMenuController@create');
    $router->put('/api/admin/menu/{id}', 'AdminMenuController@update');
    $router->delete('/api/admin/menu/{id}', 'AdminMenuController@delete');
    
    // Analytics
    $router->get('/api/admin/analytics', 'AnalyticsController@dashboard');
    $router->get('/api/admin/analytics/orders', 'AnalyticsController@orders');
    
    // QR Code generation
    $router->post('/api/admin/qr/generate', 'QrController@generate');
    $router->get('/api/admin/qr/batch', 'QrController@batchGenerate');
});

// Dispatch request
try {
    $response = $router->dispatch($request);
    $response->send();
} catch (\ProgressiveBar\Exceptions\NotFoundException $e) {
    Response::json([
        'success' => false,
        'error' => 'Endpoint not found'
    ], 404);
} catch (\ProgressiveBar\Exceptions\MethodNotAllowedException $e) {
    Response::json([
        'success' => false,
        'error' => 'Method not allowed'
    ], 405);
}
