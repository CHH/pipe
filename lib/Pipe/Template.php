<?php

namespace Pipe\Template;

use Pipe\Context;

class Template
{
    protected $file;

    /**
     * Template's content
     * @var string
     */
    protected $data;

    function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Renders the template and returns its content
     * 
     * @param  array $data
     * @return string
     */
    function render(Context $context, $vars = null)
    {
        return $this->evaluate($context, $data);
    }

    function evaluate(Context $context, $vars = null)
    {
        return $this->getData();
    }

    function prepare()
    {}

    function getData()
    {
        return $this->data ?: $this->data = @file_get_contents($this->file);
    }
}
