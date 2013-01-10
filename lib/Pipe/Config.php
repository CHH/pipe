<?php

namespace Pipe;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public
        $filename,
        $precompile,
        $precompilePrefix,
        $loadPaths = array(),
        $jsCompressor,
        $cssCompressor,
        $debug = false;

    # Public: Creates a config object from the YAML file.
    #
    # Returns a new Config object.
    static function fromYaml($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("Config file '$file' not found.");
        }

        $values = Yaml::parse(file_get_contents($file));
        $config = new static($values, $file);

        return $config;
    }

    function __construct($values = array(), $filename = null)
    {
        foreach ($values as $key => $value) {
            # Convert from underscore_separated to camelCase
            $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

            $this->$key = $value;
        }

        $this->filename = $filename;
    }

    # Creates an environment from the config keys.
    #
    # Returns a new Environment instance.
    function createEnvironment()
    {
        $env = new Environment;

        $loadPaths = $this->loadPaths ?: array();

        foreach ($loadPaths as $path) {
            $env->appendPath(dirname($this->filename) . "/" . $path);
        }

        if (!$this->debug) {
            if ($jsCompressor = $this->jsCompressor) {
                $env->setJsCompressor($jsCompressor);
            }

            if ($cssCompressor = $this->cssCompressor) {
                $env->setCssCompressor($cssCompressor);
            }
        }

        return $env;
    }
}
