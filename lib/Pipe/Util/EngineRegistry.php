<?php

namespace Pipe\Util;

class EngineRegistry extends \ArrayObject
{
    function register($extension, $engine)
    {
        if (!class_exists($engine)) {
            throw new \InvalidArgumentException("Class $engine is not defined");
        }

        if (!is_subclass_of($engine, "\\Pipe\\Template\\Base")) {
            throw new \InvalidArgumentException(sprintf(
                "A Processor must be a subclass of \\Pipe\\Template\\Base, subclass 
                of %s given",
                get_parent_class($engine)
            ));
        }

        $extension = Pathname::normalizeExtension($extension);
        $this[$extension] = $engine;
        return $this;
    }

    function get($extension)
    {
        $extension = Pathname::normalizeExtension($extension);
        if (empty($this[$extension])) {
            return;
        }
        return $this[$extension];
    }
}
