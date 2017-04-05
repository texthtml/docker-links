<?php

namespace TH\Docker;

class Link
{
    private $name;
    private $ports;
    private $env;

    /**
     * @param  string $name
     */
    public function __construct($name, array $ports, array $env)
    {
        $this->name  = $name;
        $this->ports = $ports;
        $this->env   = $env;
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

    public function env($name = null, $default = null)
    {
        if ($name === null) {
            return $this->env;
        }
        if (array_key_exists($name, $this->env)) {
            return $this->env[$name];
        }
        return $default;
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

    public static function build(array $env)
    {
        return new Link(
            $env['NAME'],
            self::buildPorts($env),
            self::buildEnv($env)
        );
    }

    private static function buildEnv(array $env)
    {
        $linkEnv = [];
        foreach ($env as $name => $value) {
            if (preg_match("/^ENV_(?<name>.*)$/", $name, $matches) === 1) {
                $linkEnv[$matches['name']] = $value;
            }
        }
        return $linkEnv;
    }

    private static function buildPorts(array $env)
    {
        return array_reduce(array_keys($env), function($linkPorts, $name) use ($env) {
            if (preg_match("/^PORT_(?<port>[0-9]+)_(?<protocol>((TCP)|(UDP)))$/", $name, $matches) === 1) {
                $linkPorts[] = Port::build($env, $matches['port'], $matches['protocol']);
            }
            return $linkPorts;
        }, []);
    }
}
