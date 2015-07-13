<?php

namespace TH\Docker;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;

class Links implements ArrayAccess, Countable, Iterator
{
    private $links;
    private $iterator;

    public function __construct(Array $links)
    {
        $this->links = $links;
        $this->iterator = new ArrayIterator($links);
    }

    public function offsetExists($alias) {
        return isset($this->links[strtoupper($alias)]);
    }

    public function offsetGet($alias) {
        return $this->links[strtoupper($alias)];
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

    public function current() {
        return $this->iterator->current();
    }

    public function key() {
        return $this->iterator->key();
    }

    public function next() {
        return $this->iterator->next();
    }

    public function rewind() {
        return $this->iterator->rewind();
    }

    public function valid() {
        return $this->iterator->valid();
    }

    public static function buildFrom(Array $env)
    {
        $links = [];
        foreach (self::envs($env) as $alias => $aliasEnv) {
            $links[$alias] = Link::build($aliasEnv, $alias);
        }
        return new self($links);
    }

    private static function envs(Array $env)
    {
        ksort($env);
        $envs = [];
        reset($env);
        foreach (self::aliases($env) as $alias) {
            while (strpos(key($env), $alias) === false) {
                next($env);
            }
            $prefixLength = strlen($alias) + 1;
            $envs[$alias] = [];
            do {
                $envs[$alias][substr(key($env), $prefixLength)] = current($env);
                next($env);
            } while (strpos(key($env), $alias) === 0);
        }
        return $envs;
    }

    private static function aliases(Array $env)
    {
        return array_filter(self::names($env), function($name) use ($env) {
            return array_key_exists("{$name}_PORT", $env);
        });
    }

    private static function names(Array $env)
    {
        $names = [];
        foreach ($env as $key => $value) {
            $keyLength = strlen($key);
            if ($keyLength < 6) {
                continue;
            }
            $pos = strrpos($key, '_NAME');
            if ($pos + 5 === $keyLength) {
                $names[] = substr($key, 0, $pos);
            }
        }
        return $names;
    }
}
