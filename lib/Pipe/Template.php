<?php

namespace Pipe;

use Pipe\Util\Pathname,
    Pipe\Util\EngineRegistry;

/**
 * The static engine registry
 */
class Template
{
    static protected $engines;

    /**
     * Returns the engine class for the given path
     *
     * @param  string $template The Template File's Path
     * @return string
     */
    static function get($template)
    {
        $extension = pathinfo($template, PATHINFO_EXTENSION);
        return static::getEngines()->get($extension);
    }

    /**
     * Creates a engine instance for the given template path
     *
     * @param  string $template
     * @param  array  $options  Engine Options to pass to the constructor
     * @return \Pipe\Template\Base
     */
    static function create($template, array $options = array())
    {
        $class    = static::get($template);
        $template = new $class($template, $options);

        return $template;
    }

    /**
     * Registers an engine with an file extension
     *
     * @param  string $engine The Engine Class
     * @param  string $extension
     * @return void
     */
    static function register($engine, $extension)
    {
        static::getEngines()->register($engine, $extension);
    }

    static function getEngines()
    {
        if (null === static::$engines) {
            static::$engines = new EngineRegistry;
        }
        return static::$engines;
    }
}

Template::register('\\Pipe\\Template\\PHPTemplate', array('php', 'phtml'));
