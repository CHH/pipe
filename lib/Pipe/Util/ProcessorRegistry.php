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
