<?php

namespace Pipe;

use Pipe\Context;

class Template
{
    protected $file;

    /**
     * Template's content
     * @var string
     */
    protected $data;

    /**
     * Engine specific options
     * @var array
     */
    protected $options;

    static protected $engineInitialized = false;

    /**
     * Constructor
     *
     * @param string|callback Either a file name, data, or a callback
     *                        which returns the template data
     * @param array $options
     */
    function __construct($fileOrData, array $options = array())
    {
        if (realpath($fileOrData)) {
            $this->file = $fileOrData;
            $this->data = @file_get_contents($fileOrData);
        
        // Call a supplied file reader
        } else if (is_callable($fileOrData)) {
            $this->data = call_user_func($fileOrData, $this);

        } else if (is_string($fileOrData)) {
            $this->data = $fileOrData;

        } else {
            throw new \InvalidArgumentException(sprintf(
                "Constructor expects either a file path, the template data
                or a file reader callback as first argument, %s given",
                gettype($fileOrData)
            ));
        }

        $this->options = $options;

        if (!static::$engineInitialized) {
            static::initEngine();
            static::$engineInitialized = true;
        }

        $this->prepare();
    }

    function prepare()
    {}

    static function initEngine()
    {}

    /**
     * Renders the template and returns its content
     * 
     * @param  array $data
     * @return string
     */
    function render(Context $context, $vars = null)
    {
        return $this->evaluate($context, $vars);
    }

    function evaluate(Context $context, $vars = null)
    {
        return $this->getData();
    }

    function setData($data)
    {
        $this->data = $data;
    }

    function getData()
    {
        return $this->data;
    }

    function getDirname()
    {
        return dirname($this->file);
    }
}
