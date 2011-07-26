<?php

namespace Pipe\Template;

use Pipe\Context;

class Base
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

    /**
     * Indicates that the underlying template engine (if any) 
     * is initialized, this happens only once
     * @var boolean
     */
    static protected $engineInitialized = false;

    /**
     * Constructor
     *
     * @param string   $file     Template File Name
     * @param callback $reader   Callback which returns the template's data
     * @param array    $options  Engine Options
     */
    function __construct($file, array $options = array(), $reader = null)
    {
        if (!file_exists($file) or !is_readable($file)) {
            throw new \InvalidArgumentException("File $file does not exist or is not readable");
        }

        $this->file = $file;
        $this->options = $options;

        if (is_callable($reader)) {
            $this->data = call_user_func($reader, $this);
        } else {
            $this->data = @file_get_contents($this->file);
        }

        // Call the initializ
        if (!static::$engineInitialized) {
            $this->initEngine();
            static::$engineInitialized = true;
        }

        $this->prepare();
    }

    /**
     * Called after the constructor
     */
    function prepare()
    {}

    /**
     * Called only once to initialize underlying engine, for example
     * require it.
     */
    function initEngine()
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

    function getLastModified()
    {
        return filemtime($this->file);
    }

    function getPath()
    {
        return $this->file;
    }

    function getBasename()
    {
        return basename($this->file);
    }

    function getDirname()
    {
        return dirname($this->file);
    }
}
