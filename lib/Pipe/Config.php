<?php

namespace Pipe;

use Symfony\Component\Yaml\Yaml;

class Config
{
    protected $storage;

    function __construct($yaml)
    {
        $this->storage = Yaml::parse($yaml);
    }

    function createEnvironment()
    {
        $env = new Environment;

        $loadPaths = $this->get('load_paths') ?: array();
        $env->appendPath($loadPaths);

        if ($jsCompressor = $this->get('js_compressor')) {
            $compressor = @$env->compressors[$jsCompressor]
            and $env->registerBundleProcessor('application/javascript', $compressor);
        }

        if ($cssCompressor = $this->get("css_compressor")) {
            $compressor = @$env->compressors[$cssCompressor]
            and $env->registerBundleProcessor('text/css', $compressor);
        }

        return $env;
    }

    function get($key)
    {
        if (array_key_exists($key, $this->storage)) {
            return $this->storage[$key];
        }
    }
}
