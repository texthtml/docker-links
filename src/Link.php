<?php

namespace TH\Docker;

class Link
{
    private $alias;
    private $name;
    private $ports;
    private $env;

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
}
