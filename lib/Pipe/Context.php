<?php

namespace Pipe;

use Pipe\Util\Pathname;

class Context extends \SplDoublyLinkedList
{
    protected $environment;

    function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function getConcatenation()
    {
        return join("\n", iterator_to_array($this));
    }
}
