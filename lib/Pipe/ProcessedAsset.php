<?php

namespace Pipe;

use Pipe\Util\Pathname;

class ProcessedAsset extends Asset
{
    # The asset's declared dependencies.
    var $dependencies = array();

    # Processes and stores the asset's body.
    #
    # Returns the body as String.
    function getBody()
    {
        if (null === $this->body) {
            $ctx    = new Context($this->environment);
            $result = "";

            $body = $ctx->evaluate($this->path, array("processors" => $this->getProcessors()));

            $this->dependencies = array_merge($this->dependencies, $ctx->dependencyPaths);

            $result .= join("\n", $ctx->dependencyAssets);
            $result .= $body;

            $this->body = $result;
        }

        return $this->body;
    }

    # Public: Calculates the date when this asset and its dependencies
    # were last modified.
    #
    # Returns a timestamp as Integer.
    function getLastModified()
    {
        # Load the asset, if it's not loaded
        if (null === $this->body) {
            $this->getBody();
        }

        $dependenciesLastModified = array_map("filemtime", $this->dependencies);
        return max(parent::getLastModified(), max($dependenciesLastModified));
    }

    # Public: Determines the asset's content type, based on its extensions.
    #
    # Returns the content type as String, or False when the content type
    # couldn't be detected.
    function getContentType()
    {
        $formatExtension = $this->getFormatExtension();

        return isset($this->environment->contentTypes[$formatExtension])
            ? $this->environment->contentTypes[$formatExtension]
            : false;
    }

    protected function getFormatExtension()
    {
        $environment = $this->environment;

        $formatExtension = current(array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return
                    isset($environment->contentTypes[$ext])
                    and !$environment->engines->get($ext);
            }
        ));

        return $formatExtension
            ?: array_search($this->getEngineContentType(), $environment->contentTypes) 
            ?: join('', $this->getExtensions());
    }

    # Collects the file's extensions.
    #
    # Returns an Array of normalized extensions.
    protected function getExtensions()
    {
        if (null === $this->extensions) {
            $basename = $this->getBasename();

            # Avoid treating name of a dotfile as extension by
            # ignoring dots at the first offset in the string
            if (!$basename or false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $extensions = explode('.', substr($basename, $pos + 1));

            $this->extensions = array_map(function($ext) {
                return Pathname::normalizeExtension($ext);
            }, $extensions);
        }

        return $this->extensions;
    }

    protected function getEngineContentType()
    {
        foreach ($this->getEngineExtensions() as $ext) {
            $engine = $this->environment->engines->get($ext);

            if (is_callable(array($engine, "getDefaultContentType"))) {
                return $engine::getDefaultContentType();
            }
        }
    }

    function getProcessors()
    {
        $contentType = $this->getContentType();

        return array_merge(
            $this->environment->preProcessors->get($contentType),
            array_reverse($this->getEngines()),
            $this->environment->postProcessors->get($contentType)
        );
    }

    protected function getEngines()
    {
        $env = $this->environment;

        return array_map(
            function($ext) use ($env) {
                return $env->engines->get($ext);
            },
            $this->getEngineExtensions()
        );
    }

    protected function getEngineExtensions()
    {
        $environment = $this->environment;

        return array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return $environment->engines->get($ext);
            }
        );
    }
}

