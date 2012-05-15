<?php

namespace Pipe;

use Symfony\Component\Yaml\Yaml;

class Config extends \ArrayObject
{
    # Maps compressor names (available as js_compressor/css_compressor) 
    # to template classes.
    var $compressors = array(
        "uglify_js" => "\\Pipe\\Compressor\\UglifyJs"
    );

    var $jsCompression = true;
    var $cssCompression = true;

    # Public: Creates a config object from the YAML file/string.
    #
    # Returns a new Config object.
    static function fromYaml($yaml)
    {
        $config = Yaml::parse($yaml);
        return new static($config);
    }

    function createEnvironment()
    {
        $env = new Environment;

        $loadPaths = $this['load_paths'] ?: array();
        $env->appendPath($loadPaths);

        if ($this->jsCompression and $jsCompressor = $this['js_compressor']) {
            if ($compressor = @$this->compressors[$jsCompressor]) {
                $env->registerBundleProcessor('application/javascript', $compressor);
            } else {
                throw new \UnexpectedValueException("JS compressor '$jsCompressor' not found.");
            }
        }

        if ($this->cssCompression and $cssCompressor = $this["css_compressor"]) {
            if ($compressor = @$this->compressors[$cssCompressor]) {
                $env->registerBundleProcessor('text/css', $compressor);
            } else {
                throw new \UnexpectedValueException("CSS compressor '$cssCompressor' not found.");
            }
        }

        return $env;
    }

    function offsetGet($key)
    {
        if (isset($this[$key])) {
            return parent::offsetGet($key);
        }
    }
}
