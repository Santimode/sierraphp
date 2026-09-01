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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        
        $body = $_POST;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (is_array($json)) $body = array_merge($body, $json);
        }

        return new self($method, $uri, $_GET, $body, $headers, [], $_SERVER);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function getMethod(): string { return $this->method; }
    public function getUri(): string { return $this->uri; }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $newAttrs = $this->attributes;
        $newAttrs[$key] = $value;
        // readonly hack via reflection for clone
        $ref = new \ReflectionObject($clone);
        $prop = $ref->getProperty('attributes');
        $prop->setValue($clone, $newAttrs);
        return $clone;
    }

    public function withAttributes(array $attrs): self
    {
        $req = $this;
        foreach ($attrs as $k => $v) {
            $req = $req->withAttribute($k, $v);
        }
        return $req;
    }
}
