<?php

namespace Pipe;

use Symfony\Component\Yaml\Yaml;

class Config
{
    # Maps compressor names (available as js_compressor/css_compressor) 
    # to template classes.
    var $compressors = array(
        "uglify_js" => "\\Pipe\\Compressor\\UglifyJs"
    );

    public $filename;

    public $precompile = array();
    public $precompilePrefix = "htdocs/assets/";
    public $loadPaths = array();

    public $jsCompressor;
    public $cssCompressor;

    # Public: Creates a config object from the YAML file/string.
    #
    # Returns a new Config object.
    static function fromYaml($yaml)
    {
        $config = Yaml::parse($yaml);
        $self = new static;
        $self->filename = $yaml;

        foreach ($config as $key => $value) {
            # Convert from underscore_separated to camelCase
            $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

            $self->$key = $value;
        }

        return $self;
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

        if ($jsCompressor = $this->jsCompressor) {
            if ($compressor = @$this->compressors[$jsCompressor]) {
                $env->registerBundleProcessor('application/javascript', $compressor);
            } else {
                throw new \UnexpectedValueException("JS compressor '$jsCompressor' not found.");
            }
        }

        if ($cssCompressor = $this->cssCompressor) {
            if ($compressor = @$this->compressors[$cssCompressor]) {
                $env->registerBundleProcessor('text/css', $compressor);
            } else {
                throw new \UnexpectedValueException("CSS compressor '$cssCompressor' not found.");
            }
        }

        return $env;
    }
}
