<?php

namespace Pipe\Util;

class ProcessorRegistry
{
    /** @var array Engines per MIME type */
    protected $processors = array();

    function prepend($mimeType, $processor)
    {
        if (!class_exists($processor)) {
            throw new \InvalidArgumentException("Class $processor is not defined");
        }

        if (empty($this->processors[$mimeType])) {
            $this->processors[$mimeType] = array();
        }

        array_unshift($this->processors[$mimeType], $processor);
        return $this;
    }

    function register($mimeType, $processor)
    {
        if (!class_exists($processor) and !is_callable($processor)) {
            throw new \InvalidArgumentException("Processor must be either a factory callback or a class name");
        }

        if (empty($this->processors[$mimeType])) {
            $this->processors[$mimeType] = array();
        }

        $this->processors[$mimeType][] = $processor;
        return $this;
    }

    function isRegistered($mimeType, $processor)
    {
        if (empty($this->processors[$mimeType])) {
            return false;
        }

        $index = array_search($processor, $this->processors[$mimeType]);

        return $index !== false;
    }

    function unregister($mimeType, $processor)
    {
        if ($this->isRegistered($mimeType, $processor)) {
            $index = array_search($processor, $this->processors[$mimeType]);
            unset($this->processors[$mimeType][$index]);
        }
    }

    function clear()
    {
        $this->processors = array();
    }

    /**
     * Returns all processor classes for the MIME type.
     *
     * @param string $mimeType
     * @return array
     */
    function all($mimeType)
    {
        if (empty($this->processors[$mimeType])) {
            return array();
        }
        return $this->processors[$mimeType];
    }
}
