<?php

namespace Pipe;

use Pipe\Util\PathStack;

class Environment implements \ArrayAccess
{
    /**
     * @var PathStack
     */
    protected $loadPaths;

    function __construct()
    {
        $this->loadPaths = new PathStack;
    }

    function getLoadPaths()
    {
        return $this->loadPaths;
    }

    function offsetGet($offset)
    {
    }

    function offsetSet($offset, $value) {}
    function offsetExists($offset) {}
    function offsetUnset($offset) {}
}
