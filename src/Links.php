<?php

namespace TH\Docker;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;

class Links implements ArrayAccess, Countable, Iterator
{
    private $env;
    private $unique;

    private $links;
    private $iterator;

    public function __construct($env, $unique)
    {
        $this->env = $env;
        $this->unique = $unique;
    }

    public function offsetExists($name) {
        return isset($this->links()[strtoupper($name)]);
    }

    public function offsetGet($name) {
        return $this->links()[strtoupper($name)];
    }

    public function offsetSet($name, $value) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function offsetUnset($name) {
        throw new \Exception('\TH\Docker\Links is read only');
    }

    public function count()
    {
        return count($this->links());
    }

    public function current() {
        return $this->iterator()->current();
    }

    public function key() {
        return $this->iterator()->key();
    }

    public function next() {
        return $this->iterator()->next();
    }

    public function rewind() {
        return $this->iterator()->rewind();
    }

    public function valid() {
        return $this->iterator()->valid();
    }

    public function withEnv($name, $value = null)
    {
        $suffix = "_ENV_".strtoupper($name);
        $n = strlen($suffix);

        $filteredEnv = array_filter($this->env, function ($key) use ($suffix, $n) {
            return substr_compare($key, $suffix, -$n) === 0;
        }, ARRAY_FILTER_USE_KEY);
        if ($value !== null) {
            $filteredEnv = array_filter($filteredEnv, function ($envValue) use ($value) {
                return $envValue === $value;
            });
        }
        $prefixes = array_map(function ($key) use ($n) {
            return substr($key, 0, -$n);
        }, array_keys($filteredEnv));
        $prefixes = array_combine($prefixes, array_map("strlen", $prefixes));
        $env = array_filter($this->env, function ($key) use ($prefixes) {
            foreach ($prefixes as $prefix => $prefixLength) {
                if (substr_compare($key, $prefix, 0, $prefixLength) === 0) {
                    return true;
                }
            }
            return false;
        }, ARRAY_FILTER_USE_KEY);
        return new self($env, $this->unique);
    }

    public static function buildFrom(Array $env, $unique = true)
    {
        return new self($env, $unique);
    }

    private function links() {
        if ($this->links === null) {
            $this->links = [];
            foreach (self::envs($this->env, $this->unique) as $prefixEnv) {
                $link = Link::build($prefixEnv);
                $this->links[strtoupper($link->name())] = $link;
            }
        }
        return $this->links;
    }

    private function iterator() {
        if ($this->iterator === null) {
            $this->iterator = new ArrayIterator($this->links());
        }
        return $this->iterator;
    }

    /**
     * @param  boolean $unique
     */
    private static function envs(Array $env, $unique)
    {
        ksort($env);
        $envs = [];
        reset($env);
        foreach (self::prefixes($env, $unique) as $prefix) {
            while (strpos(key($env), $prefix) === false) {
                next($env);
            }
            $prefixLength = strlen($prefix) + 1;
            $prefixEnv = [];
            do {
                $prefixEnv[substr(key($env), $prefixLength)] = current($env);
                next($env);
            } while (strpos(key($env), $prefix) === 0);
            $envs[] = $prefixEnv;
        }
        return $envs;
    }

    /**
     * @param  boolean $unique
     */
    private static function prefixes(Array $env, $unique)
    {
        return $unique ? self::uniquePrefixes($env) : self::prefixesWithPorts($env);
    }

    private static function uniquePrefixes(Array $env)
    {
        $prefixes = self::prefixesWithPorts($env);
        sort($prefixes);
        return array_reduce($prefixes, function($uniquePrefixes, $prefix) {
            foreach ($uniquePrefixes as $uniqueprefix) {
                if (strpos($prefix, $uniqueprefix) !== false || strpos($uniqueprefix, $prefix) !== false) {
                    return $uniquePrefixes;
                }
            }
            $uniquePrefixes[] = $prefix;
            return $uniquePrefixes;
        }, []);
    }

    private static function prefixesWithPorts(Array $env)
    {
        return array_filter(self::allPrefixes($env), function($prefix) use ($env) {
            return array_key_exists("{$prefix}_PORT", $env);
        });
    }

    private static function allPrefixes(Array $env)
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
