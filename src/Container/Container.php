<?php
declare(strict_types=1);
namespace Sierra\Container;

use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $id, \Closure|string $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function singleton(string $id, \Closure|string $concrete): void
    {
        $this->singletons[$id] = $concrete;
    }

    public function instance(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->singletons[$id])) {
            $concrete = $this->singletons[$id];
            $object = $concrete instanceof \Closure ? $concrete($this) : $this->make($concrete);
            $this->instances[$id] = $object;
            return $object;
        }

        if (isset($this->bindings[$id])) {
            $concrete = $this->bindings[$id];
            return $concrete instanceof \Closure ? $concrete($this) : $this->make($concrete);
        }

        // auto-wire
        if (class_exists($id)) {
            return $this->make($id);
        }

        throw new \RuntimeException("No binding found for {$id}");
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->singletons[$id]) || isset($this->bindings[$id]) || class_exists($id);
    }

    public function make(string $class, array $params = []): object
    {
        $ref = new \ReflectionClass($class);
        if (!$ref->isInstantiable()) {
            throw new \RuntimeException("{$class} is not instantiable");
        }
        $ctor = $ref->getConstructor();
        if (!$ctor) {
            return new $class();
        }
        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
                continue;
            }
            if ($type && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve param \${$name} for {$class}");
            }
        }
        return $ref->newInstanceArgs($args);
    }
}
