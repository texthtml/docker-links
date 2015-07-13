<?php

namespace TH\Docker;

class Link
{
    private $alias;
    private $name;
    private $ports;
    private $env;

    /**
     * @param  string $alias
     */
    public function __construct($alias, $name, Array $ports, Array $env)
    {
        $this->alias = $alias;
        $this->name  = $name;
        $this->ports = $ports;
        $this->env   = $env;
    }

    public function alias()
    {
        return $this->alias;
    }

    public function name()
    {
        return $this->name;
    }

    public function ports($protocol = null)
    {
        if ($protocol === null) {
            return $this->ports;
        }
        return array_reduce($this->ports, function($ports, Port $port) use ($protocol) {
            if ($port->protocol() === $protocol) {
                $ports[$port->number()] = $port;
            }
            return $ports;
        }, []);
    }

    public function env()
    {
        return $this->env;
    }

    public function mainPort()
    {
        $ports = $this->ports();
        return reset($ports);
    }

    public function tcpPorts()
    {
        return $this->ports(Port::TCP);
    }

    public function udpPorts()
    {
        return $this->ports(Port::UDP);
    }

    /**
     * @param  string $alias
     */
    public static function build(Array $env, $alias)
    {
        return new Link(
            $alias,
            $env['NAME'],
            self::buildPorts($env),
            self::buildEnv($env)
        );
    }

    private static function buildEnv(Array $env)
    {
        $linkEnv = [];
        foreach ($env as $name => $value) {
            if (preg_match("/^ENV_(?<name>.*)$/", $name, $matches) === 1) {
                $linkEnv[$matches['name']] = $value;
            }
        }
        return $linkEnv;
    }

    private static function buildPorts(Array $env)
    {
        return array_reduce(array_keys($env), function($linkPorts, $name) use ($env) {
            if (preg_match("/^PORT_(?<port>[0-9]+)_(?<protocol>((TCP)|(UDP)))$/", $name, $matches) === 1) {
                $linkPorts[] = Port::build($env, $matches['port'], $matches['protocol']);
            }
            return $linkPorts;
        }, []);
    }
}
