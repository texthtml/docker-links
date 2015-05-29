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
        $links = [];
        foreach ($env as $name => $value) {
            if (preg_match('/^(?<alias>[A-Z0-9_\.]+)_NAME$/', $name, $matches) === 1) {
                $links[$matches['alias']] = self::buildLink($env, $matches['alias']);
            }
        }
        return new static($links);
    }

    /**
     * @param  string $alias
     */
    private static function buildLink(Array $env, $alias)
    {
        $linkPorts = [];
        $linkEnv = [];
        $portRegexp = "/^{$alias}_PORT_(?<port>[0-9]+)_(?<protocol>((TCP)|(UDP)))$/";
        foreach ($env as $name => $value) {
            if (preg_match($portRegexp, $name, $matches) === 1) {
                $linkPorts[] = self::buildPort($env, $alias, $matches['port'], $matches['protocol']);
            }
        }
        return new Link(
            $alias,
            $env["{$alias}_NAME"],
            $linkPorts,
            $linkEnv
        );
    }

    /**
     * @param  string $alias
     * @param  int    $port
     */
    private static function buildPort(Array $env, $alias, $port, $protocol)
    {
        $prefix = "{$alias}_PORT_{$port}_{$protocol}";
        return new Port(
            $env["{$prefix}_ADDR"],
            $env["{$prefix}_PORT"],
            $env["{$prefix}_PROTO"]
        );
    }
}
