<?php

namespace Pipe;

class Context extends \SplDoublyLinkedList
{
    protected $environment;

    function __construct(Environment $env)
    {
        $this->environment = $env;
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function toString()
    {
    }
}
