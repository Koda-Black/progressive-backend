<?php
/**
 * Simple Router Implementation
 */

declare(strict_types=1);

namespace ProgressiveBar\Core;

use ProgressiveBar\Exceptions\NotFoundException;
use ProgressiveBar\Exceptions\MethodNotAllowedException;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private string $controllerNamespace = 'ProgressiveBar\\Controllers\\';

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function group(array $options, callable $callback): void
    {
        $this->groupStack[] = $options;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        // Apply group middleware
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array)$group['middleware']);
            }
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $this->pathToPattern($path),
        ];
    }

    private function pathToPattern(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            return new Response(204);
        }

        $matchedRoute = null;
        $params = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                if ($route['method'] !== $method) {
                    continue; // Method doesn't match, but path does
                }

                $matchedRoute = $route;
                
                // Extract named parameters
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                break;
            }
        }

        if ($matchedRoute === null) {
            // Check if path exists but method is wrong
            foreach ($this->routes as $route) {
                if (preg_match($route['pattern'], $uri)) {
                    throw new MethodNotAllowedException();
                }
            }
            throw new NotFoundException();
        }

        // Apply route middleware
        foreach ($matchedRoute['middleware'] as $middlewareClass) {
            $middleware = new $middlewareClass();
            $request = $middleware->handle($request);
            
            if ($request->getAttribute('terminate')) {
                return new Response(200);
            }
        }

        // Store params in request
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        // Execute handler
        return $this->executeHandler($matchedRoute['handler'], $request, $params);
    }

    private function executeHandler($handler, Request $request, array $params): Response
    {
        // Callable (closure)
        if (is_callable($handler)) {
            $result = $handler($request, $params);
            
            if ($result instanceof Response) {
                return $result;
            }
            
            // If returned array, convert to JSON response
            if (is_array($result)) {
                Response::json($result);
                return new Response(200);
            }
            
            return new Response(200);
        }

        // Controller@method string
        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerName, $methodName] = explode('@', $handler);
            
            $controllerClass = $this->controllerNamespace . $controllerName;
            
            if (!class_exists($controllerClass)) {
                throw new NotFoundException("Controller not found: $controllerClass");
            }

            $controller = new $controllerClass();
            
            if (!method_exists($controller, $methodName)) {
                throw new NotFoundException("Method not found: $methodName");
            }

            $result = $controller->$methodName($request, $params);
            
            if ($result instanceof Response) {
                return $result;
            }
            
            return new Response(200);
        }

        throw new \RuntimeException('Invalid route handler');
    }
}
