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

    public function offsetExists($name) {
        return isset($this->links[strtoupper($name)]);
    }

    public function offsetGet($name) {
        return $this->links[strtoupper($name)];
    }

    public function offsetSet($name, $value) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function offsetUnset($name) {
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

    public static function buildFrom(Array $env, $unique = true)
    {
        $links = [];
        foreach (self::envs($env, $unique) as $rpefix => $rpefixEnv) {
            $link = Link::build($rpefixEnv, $rpefix);
            $links[strtoupper($link->name())] = $link;
        }
        return new self($links);
    }

    private static function envs(Array $env)
    {
        ksort($env);
        $envs = [];
        reset($env);
        foreach (self::uniquePrefixes($env) as $rpefix) {
            while (strpos(key($env), $rpefix) === false) {
                next($env);
            }
            $prefixLength = strlen($rpefix) + 1;
            $envs[$rpefix] = [];
            do {
                $envs[$rpefix][substr(key($env), $prefixLength)] = current($env);
                next($env);
            } while (strpos(key($env), $rpefix) === 0);
        }
        return $envs;
    }

    private static function uniquePrefixes(Array $env)
    {
        $prefixes = self::prefixesWithPorts($env);
        sort($prefixes);
        $uniquePrefixes = [];
        foreach ($prefixes as $prefix) {
            foreach ($uniquePrefixes as $uniqueprefix) {
                if (strpos($prefix, $uniqueprefix) !== false || strpos($uniqueprefix, $prefix) !== false) {
                    continue 2;
                }
            }
            $uniquePrefixes[] = $prefix;
        }
        return $uniquePrefixes;
    }

    private static function prefixesWithPorts(Array $env)
    {
        return array_filter(self::prefixes($env), function($prefix) use ($env) {
            return array_key_exists("{$prefix}_PORT", $env);
        });
    }

    private static function prefixes(Array $env)
    {
        $prefixes = [];
        foreach ($env as $key => $value) {
            $keyLength = strlen($key);
            if ($keyLength < 6) {
                continue;
            }
            $pos = strrpos($key, '_NAME');
            if ($pos + 5 === $keyLength) {
                $prefixes[] = substr($key, 0, $pos);
            }
        }
        return $prefixes;
    }
}
