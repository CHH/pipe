<?php

namespace Pipe\Util;

class ProcessorRegistry
{
    protected $processors = array();

    function register($mimeType, $processor)
    {
        if (!class_exists($processor)) {
            throw new \InvalidArgumentException("Class $processor is not defined");
        }

        if (!is_subclass_of($processor, "\\Pipe\\Template\\Base")) {
            throw new \InvalidArgumentException(sprintf(
                "A Processor must be a subclass of \\Pipe\\Template\\Base, subclass 
                of %s given",
                get_parent_class($processor)
            ));
        }

        if (empty($this->processors[$mimeType])) {
            $this->processors[$mimeType] = array();
        }

        $this->processors[$mimeType][] = $processor;
        return $this;
    }

    function get($mimeType)
    {
        if (empty($this->processors[$mimeType])) {
            return array();
        }
        return $this->processors[$mimeType];
    }
}
