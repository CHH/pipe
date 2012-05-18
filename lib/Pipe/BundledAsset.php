<?php

namespace Pipe;

class BundledAsset
{
    var $path;
    var $logicalPath;

    protected $environment;
    protected $body;

    protected $processedAsset;

    function __construct(Environment $environment, $path, $logicalPath = null)
    {
        $this->environment = $environment;
        $this->path = $path;
        $this->logicalPath = $logicalPath;

        $this->processedAsset = $this->environment->find($path);
    }

    function getBody()
    {
        if (null === $this->body) {
            $body = $this->processedAsset->getBody();

            $bundleProcessors = $this->environment->bundleProcessors->get($this->getContentType());
            $context = new Context($this->environment);

            $this->body = $context->evaluate($this->path, array(
                "processors" => $bundleProcessors,
                "data" => $body
            ));
        }

        return $this->body;
    }

    function __toString()
    {
        return $this->getBody();
    }

    function __call($method, $argv)
    {
        return call_user_func_array(array($this->processedAsset, $method), $argv);
    }
}
