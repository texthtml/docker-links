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
}
