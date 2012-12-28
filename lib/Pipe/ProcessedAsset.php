<?php

namespace Pipe;

class ProcessedAsset extends Asset
{
    # Processes and stores the asset's body.
    #
    # Returns the body as String.
    function getBody()
    {
        if (null === $this->body) {
            $ctx    = new Context($this->environment);
            $result = '';

            $body = $ctx->evaluate($this->path, array(
                "data" => parent::getBody(),
                "processors" => $this->getProcessors()
            ));

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

        $dependenciesLastModified = array_merge(array_map("filemtime", $this->dependencies), array(parent::getLastModified()));
        return max($dependenciesLastModified);
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

    function getProcessors()
    {
        $contentType = $this->getContentType();

        return array_merge(
            $this->environment->preProcessors->get($contentType),
            array_reverse($this->getEngines()),
            $this->environment->postProcessors->get($contentType)
        );
    }
}

