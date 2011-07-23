<?php

namespace Pipe;

use Pipe\Util\Pathname;

class Context extends \SplDoublyLinkedList
{
    protected $environment;

    function __construct(Environment $env)
    {
        $this->environment = $env;
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function has($pathOrAsset)
    {
        if ($pathOrAsset instanceof Asset) {
            $pathOrAsset = $pathOrAsset->getPath();
        }

        foreach ($this as $asset) {
            if ($asset instanceof Asset and $asset->getPath() === $pathOrAsset) {
                return true;
            }
        }
        return false;
    }

    function getConcatenation()
    {
        $concatenation = '';

        foreach ($this as $part) {
            if ($part instanceof Asset) {
                $concatenation .= $this->process($part);
            } else {
                $concatenation .= $part;
            }
        }
        return $concatenation;
    }

    function process(Asset $asset)
    {
        $content = file_get_contents($asset->getPath());

        $preProcessors = $this->environment->getPreProcessors();
        $content = $this->runProcessors($content, $preProcessors);

        $processors = array();
        foreach ($asset->getExtensions() as $ext) {
            $mimeType = $this->environment->getMimeType($ext);
            $processors = array_merge(
                $processors, 
                $this->environment->getProcessorsForMimeType($mimeType)
            );
        }

        $content = $this->runProcessors($content, $processors);
        return $content;
    }

    function runProcessors($content, array $processors)
    {
        foreach ($processors as $processorClass) {
            $processor = new $processorClass($content);
            $content = $processor->render($this);
        }
        return $content;
    }

    function toString()
    {
    }
}
