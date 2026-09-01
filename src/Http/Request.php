<?php
declare(strict_types=1);
namespace Sierra\Http;
final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $query = [],
        public readonly array $body = [],
        public readonly array $headers = [],
        public readonly array $attributes = [],
        public readonly array $server = [],
    ) {}
    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $body = $_POST;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) $body = array_merge($body, $json);
        }

        if ($method === 'POST') {
            if (isset($body['_method']) && is_string($body['_method'])) {
                $method = strtoupper($body['_method']);
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = strtoupper((string)$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }

        return new self($method, $uri, $_GET, $body, $headers, [], $_SERVER);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->all();
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->query;
        return $this->query[$key] ?? $default;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->all();
        return $this->input($key, $default);
    }

    public function all(): array { return array_merge($this->query, $this->body); }
    public function getMethod(): string { return $this->method; }
    public function getUri(): string { return $this->uri; }
    public function getHeaders(): array { return $this->headers; }

    public function header(string $key, ?string $default = null): ?string
    {
        foreach ($this->headers as $k => $v) {
            if (strcasecmp($k, $key) === 0) {
                return (string)$v;
            }
        }

        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        if (isset($this->server[$serverKey])) {
            return (string)$this->server[$serverKey];
        }

        if (strcasecmp($key, 'Content-Type') === 0 && isset($this->server['CONTENT_TYPE'])) {
            return (string)$this->server['CONTENT_TYPE'];
        }

        return $default;
    }

    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    public function isJson(): bool
    {
        $contentType = (string)($this->header('Content-Type') ?? ($this->server['CONTENT_TYPE'] ?? ''));
        return str_contains($contentType, '/json') || str_contains($contentType, '+json');
    }

    public function expectsJson(): bool
    {
        if ($this->isJson()) {
            return true;
        }

        $accept = (string)($this->header('Accept') ?? ($this->server['HTTP_ACCEPT'] ?? ''));
        if (str_contains($accept, '/json') || str_contains($accept, '+json')) {
            return true;
        }

        $requestedWith = (string)($this->header('X-Requested-With') ?? ($this->server['HTTP_X_REQUESTED_WITH'] ?? ''));
        if (strcasecmp($requestedWith, 'XMLHttpRequest') === 0) {
            return true;
        }

        return str_starts_with($this->uri, '/api');
    }

    public function getAttribute(string $key, mixed $default = null): mixed { return $this->attributes[$key] ?? $default; }
    public function withAttribute(string $key, mixed $value): self
    {
        $newAttributes = $this->attributes;
        $newAttributes[$key] = $value;
        return new self($this->method, $this->uri, $this->query, $this->body, $this->headers, $newAttributes, $this->server);
    }
    public function withAttributes(array $attrs): self
    {
        return new self($this->method, $this->uri, $this->query, $this->body, $this->headers, array_merge($this->attributes, $attrs), $this->server);
    }
}
