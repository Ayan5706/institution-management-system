<?php

/**
 * Mock Helper Class
 * 
 * Utilities for creating mocks and stubs
 */

class MockHelper
{
    /**
     * Create a mock logger
     */
    public static function createMockLogger()
    {
        $mock = new class {
            private array $logs = [];

            public function info(string $message, array $context = []): void
            {
                $this->logs[] = ['level' => 'info', 'message' => $message, 'context' => $context];
            }

            public function warning(string $message, array $context = []): void
            {
                $this->logs[] = ['level' => 'warning', 'message' => $message, 'context' => $context];
            }

            public function error(string $message, array $context = []): void
            {
                $this->logs[] = ['level' => 'error', 'message' => $message, 'context' => $context];
            }

            public function debug(string $message, array $context = []): void
            {
                $this->logs[] = ['level' => 'debug', 'message' => $message, 'context' => $context];
            }

            public function getLogs(): array
            {
                return $this->logs;
            }

            public function clear(): void
            {
                $this->logs = [];
            }
        };

        return $mock;
    }

    /**
     * Create a mock cache
     */
    public static function createMockCache()
    {
        $mock = new class {
            private array $cache = [];
            private array $ttl = [];

            public function put(string $key, $value, int $ttl = 3600): void
            {
                $this->cache[$key] = $value;
                $this->ttl[$key] = time() + $ttl;
            }

            public function get(string $key)
            {
                if (!isset($this->cache[$key])) {
                    return null;
                }

                if (time() > $this->ttl[$key]) {
                    unset($this->cache[$key]);
                    return null;
                }

                return $this->cache[$key];
            }

            public function has(string $key): bool
            {
                return $this->get($key) !== null;
            }

            public function forget(string $key): void
            {
                unset($this->cache[$key]);
                unset($this->ttl[$key]);
            }

            public function flush(): void
            {
                $this->cache = [];
                $this->ttl = [];
            }

            public function getAll(): array
            {
                return $this->cache;
            }
        };

        return $mock;
    }

    /**
     * Create a mock session
     */
    public static function createMockSession()
    {
        $mock = new class {
            private array $data = [];
            private array $flash = [];

            public function put(string $key, $value): void
            {
                $this->data[$key] = $value;
            }

            public function get(string $key, $default = null)
            {
                return $this->data[$key] ?? $default;
            }

            public function has(string $key): bool
            {
                return isset($this->data[$key]);
            }

            public function forget(string $key): void
            {
                unset($this->data[$key]);
            }

            public function flash(string $key, $value): void
            {
                $this->flash[$key] = $value;
            }

            public function getFlash(string $key, $default = null)
            {
                $value = $this->flash[$key] ?? $default;
                unset($this->flash[$key]);
                return $value;
            }

            public function getAll(): array
            {
                return $this->data;
            }

            public function clear(): void
            {
                $this->data = [];
                $this->flash = [];
            }
        };

        return $mock;
    }

    /**
     * Create a mock database
     */
    public static function createMockDatabase()
    {
        $mock = new class {
            private array $tables = [];
            private array $lastQuery = [];

            public function query(string $sql, array $params = []): self
            {
                $this->lastQuery = ['sql' => $sql, 'params' => $params];
                return $this;
            }

            public function insert(string $table, array $data): int
            {
                $this->lastQuery = ['action' => 'insert', 'table' => $table, 'data' => $data];
                $this->tables[$table][] = $data;
                return count($this->tables[$table] ?? []);
            }

            public function update(string $table, array $data, array $where): int
            {
                $this->lastQuery = ['action' => 'update', 'table' => $table, 'data' => $data, 'where' => $where];
                return 1;
            }

            public function delete(string $table, array $where): int
            {
                $this->lastQuery = ['action' => 'delete', 'table' => $table, 'where' => $where];
                return 1;
            }

            public function select(string $query): array
            {
                return $this->tables[$query] ?? [];
            }

            public function getLastQuery(): array
            {
                return $this->lastQuery;
            }

            public function getTable(string $name): array
            {
                return $this->tables[$name] ?? [];
            }

            public function setTable(string $name, array $data): void
            {
                $this->tables[$name] = $data;
            }

            public function clear(): void
            {
                $this->tables = [];
                $this->lastQuery = [];
            }
        };

        return $mock;
    }

    /**
     * Create a mock request
     */
    public static function createMockRequest(array $data = [], string $method = 'GET')
    {
        $mock = new class($data, $method) {
            private array $data;
            private string $method;

            public function __construct(array $data, string $method)
            {
                $this->data = $data;
                $this->method = $method;
            }

            public function input(string $key, $default = null)
            {
                return $this->data[$key] ?? $default;
            }

            public function all(): array
            {
                return $this->data;
            }

            public function has(string $key): bool
            {
                return isset($this->data[$key]);
            }

            public function method(): string
            {
                return $this->method;
            }

            public function isPost(): bool
            {
                return $this->method === 'POST';
            }

            public function isGet(): bool
            {
                return $this->method === 'GET';
            }
        };

        return $mock;
    }

    /**
     * Create a mock response
     */
    public static function createMockResponse()
    {
        $mock = new class {
            private int $statusCode = 200;
            private array $data = [];
            private array $headers = [];

            public function status(int $code): self
            {
                $this->statusCode = $code;
                return $this;
            }

            public function json(array $data): self
            {
                $this->data = $data;
                return $this;
            }

            public function header(string $key, string $value): self
            {
                $this->headers[$key] = $value;
                return $this;
            }

            public function getStatus(): int
            {
                return $this->statusCode;
            }

            public function getData(): array
            {
                return $this->data;
            }

            public function getHeaders(): array
            {
                return $this->headers;
            }
        };

        return $mock;
    }

    /**
     * Create a spy for a method
     * 
     * @param object $object
     * @param string $methodName
     * @return callable
     */
    public static function spyOn(object $object, string $methodName): callable
    {
        $calls = [];
        $originalMethod = $object->$methodName ?? null;

        return function (...$args) use (&$calls, $object, $methodName, $originalMethod) {
            $calls[] = $args;
            if (is_callable($originalMethod)) {
                return $originalMethod(...$args);
            }
            return null;
        };
    }
}
