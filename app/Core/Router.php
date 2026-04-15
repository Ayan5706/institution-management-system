<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use Throwable;

final class Router
{
    /** @var array<string, array<int, array{path:string,handler:callable|string,middleware:array<int, string|callable|MiddlewareInterface>}>> */
    private array $routes = [];

    /** @var array<string, class-string<MiddlewareInterface>> */
    private array $middlewareAliases = [];

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    public function get(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    public function post(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    public function put(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    public function patch(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    public function delete(string $path, callable|string $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function alias(string $name, string $middlewareClass): void
    {
        $this->middlewareAliases[$name] = $middlewareClass;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $this->normalizePath($request->path());

        foreach ($this->routes[$method] ?? [] as $route) {
            $routePath = $this->normalizePath($route['path']);
            $params = $this->match($routePath, $path);

            if ($params === null) {
                continue;
            }

            if (!$this->runMiddleware($route['middleware'], $request, $params)) {
                return;
            }

            $this->runHandler($route['handler'], $request, $params);
            return;
        }

        http_response_code(404);
        echo 'Route not found';
    }

    /** @param array<int, string|callable|MiddlewareInterface> $middleware */
    private function add(string $method, string $path, callable|string $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }

    /** @return array<string, string>|null */
    private function match(string $routePath, string $requestPath): ?array
    {
        if ($routePath === $requestPath) {
            return [];
        }

        $routeSegments = explode('/', trim($routePath, '/'));
        $requestSegments = explode('/', trim($requestPath, '/'));

        if (count($routeSegments) !== count($requestSegments)) {
            return null;
        }

        $params = [];

        foreach ($routeSegments as $i => $segment) {
            $requestSegment = $requestSegments[$i] ?? '';

            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $matches) === 1) {
                $params[$matches[1]] = $requestSegment;
                continue;
            }

            if ($segment !== $requestSegment) {
                return null;
            }
        }

        return $params;
    }

    /** @param array<string, string> $params */
    private function runHandler(callable|string $handler, Request $request, array $params): void
    {
        try {
            if (is_callable($handler)) {
                $handler($request, $params);
                return;
            }

            if (is_string($handler) && str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler, 2);

                if (!class_exists($class)) {
                    throw new \RuntimeException('Controller class not found: ' . $class);
                }

                $controller = new $class();

                if (!method_exists($controller, $method)) {
                    throw new \RuntimeException('Controller method not found: ' . $class . '@' . $method);
                }

                if ($params === []) {
                    $controller->{$method}();
                    return;
                }

                // Cast parameters to their declared types using Reflection
                $reflection = new \ReflectionMethod($controller, $method);
                $callParams = [];
                
                foreach ($reflection->getParameters() as $param) {
                    $paramName = $param->getName();
                    $value = $params[$paramName] ?? null;
                    $type = $param->getType();
                    
                    // Type cast based on the parameter's type hint
                    if ($type && $type->isBuiltin()) {
                        $typeName = $type->getName();
                        if ($typeName === 'int') {
                            $value = (int) $value;
                        } elseif ($typeName === 'float') {
                            $value = (float) $value;
                        } elseif ($typeName === 'bool') {
                            $value = (bool) $value;
                        } elseif ($typeName === 'string') {
                            $value = (string) $value;
                        }
                    }
                    
                    $callParams[] = $value;
                }

                $controller->{$method}(...$callParams);
                return;
            }

            throw new \RuntimeException('Invalid route handler');
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Application error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * @param array<int, string|callable|MiddlewareInterface> $definitions
     * @param array<string, string> $params
     */
    private function runMiddleware(array $definitions, Request $request, array $params): bool
    {
        foreach ($definitions as $definition) {
            if ($definition instanceof MiddlewareInterface) {
                if (!$definition->handle($request, $params)) {
                    return false;
                }

                continue;
            }

            if (is_callable($definition)) {
                if ($definition($request, $params) === false) {
                    return false;
                }

                continue;
            }

            if (!is_string($definition) || $definition === '') {
                throw new \RuntimeException('Invalid middleware definition');
            }

            [$alias, $argumentString] = array_pad(explode(':', $definition, 2), 2, null);
            $class = $this->middlewareAliases[$alias] ?? $alias;

            if (!class_exists($class)) {
                throw new \RuntimeException('Middleware class not found: ' . $class);
            }

            $arguments = [];

            if (is_string($argumentString) && $argumentString !== '') {
                $arguments = array_map('trim', explode(',', $argumentString));
            }

            $middleware = new $class(...$arguments);

            if (!$middleware instanceof MiddlewareInterface) {
                throw new \RuntimeException('Middleware must implement MiddlewareInterface: ' . $class);
            }

            if (!$middleware->handle($request, $params)) {
                return false;
            }
        }

        return true;
    }
}
