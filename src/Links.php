<?php

namespace TH\Docker;

use ArrayAccess;
use Countable;

class Links implements ArrayAccess, Countable
{
    private $links;

    public function __construct(Array $links)
    {
        $this->links = $links;
    }

    public function offsetExists($alias) {
        return isset($this->links[$alias]);
    }

    public function offsetGet($alias) {
        return $this->links[$alias];
    }

    public function offsetSet($alias, $value) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function offsetUnset($alias) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function count()
    {
        return count($this->links);
    }

    public static function buildFromEnv()
    {
        return static::buildFrom($_ENV);
    }

    public static function buildFrom(Array $env)
    {
        return new static(array_reduce(array_keys($env), function($links, $name) use ($env) {
            if (preg_match('/^(?<alias>[A-Z0-9_\.]+)_NAME$/', $name, $matches) === 1) {
                $links[$matches['alias']] = Link::build($env, $matches['alias']);
            }
            return $links;
        }, []));
    }
}
