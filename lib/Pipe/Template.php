<?php

namespace Pipe;

use Pipe\Util\Pathname;

class Template
{
    static protected $engines = array();

    /**
     * Returns the engine class for the given path
     *
     * @param  string $template The Template File's Path
     * @return string
     */
    static function get($template)
    {
        $extension = pathinfo($template, PATHINFO_EXTENSION);
        $extension = Pathname::normalizeExtension($extension);

        if (!empty(static::$engines[$extension])) {
            return static::$engines[$extension];
        }
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
        if (!class_exists($engine)) {
            throw new \InvalidArgumentException("Engine Class \"$engine\" not found.");
        }

        if (!is_subclass_of($engine, '\\Pipe\\Template\\Base')) {
            throw new \RuntimeException(sprintf(
                "Template engines must inherit from \\Pipe\\Engine\\Base, 
                subclass of %s given.",
                get_parent_class($engine)
            ));
        }

        $extensions = (array) $extension;

        foreach ($extensions as $e) {
            $e = Pathname::normalizeExtension($e);
            static::$engines[$e] = $engine;
        }
    }
}

Template::register('\\Pipe\\Template\\PhpTemplate', array('php', 'phtml'));
