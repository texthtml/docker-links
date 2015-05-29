<?php

namespace TH\Docker;

class Port
{
    const TCP = "TCP";
    const UDP = "UDP";

    private $address;
    private $number;
    private $protocol;

    public function __construct($address, $number, $protocol)
    {
        $this->address  = $address;
        $this->number   = (int)$number;
        $this->protocol = strtoupper($protocol);
    }

    public function address()
    {
        return $this->address;
    }

    public function number()
    {
        return $this->number;
    }

    public function protocol()
    {
        return $this->protocol;
    }

    /**
     * @param  string $alias
     * @param  int    $port
     */
    public static function build(Array $env, $alias, $port, $protocol)
    {
        $prefix = "{$alias}_PORT_{$port}_{$protocol}";
        return new Port(
            $env["{$prefix}_ADDR"],
            $env["{$prefix}_PORT"],
            $env["{$prefix}_PROTO"]
        );
    }
}
