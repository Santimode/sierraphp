<?php
declare(strict_types=1);
namespace Sierra\Router;

final class Route
{
    public array $middleware = [];
    public ?string $name = null;
    public array $wheres = [];

    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly mixed $handler,
    ) {}

    public function middleware(string|array $mw): self
    {
        $this->middleware = array_merge($this->middleware, (array)$mw);
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function where(string|array $param, ?string $regex = null): self
    {
        if (is_array($param)) {
            $this->wheres = array_merge($this->wheres, $param);
        } else {
            $this->wheres[$param] = $regex;
        }
        return $this;
    }
}
