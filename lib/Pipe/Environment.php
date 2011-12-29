<?php

namespace Pipe;

use Pipe\Util\Pathstack,
    Pipe\Util\Pathname,
    Pipe\Util\ProcessorRegistry,
    MetaTemplate\Template,
    MetaTemplate\Util\EngineRegistry,
    Symfony\Component\Finder\Finder;

class Environment implements \ArrayAccess
{
    /**
     * @var Pathstack
     */
    var $loadPaths;

    /**
     * @var ContentTypeRegistry
     */
    var $contentTypes;

    /**
     * Engines per file extension
     * @var EngineRegistry
     */
    var $engines;

    /**
     * Processors are like engines, but are associated with
     * a specific content type and one processor can be 
     * associated with one or more content types
     */
    protected $preProcessors;
    protected $postProcessors;

    function __construct()
    {
        $this->loadPaths = new Pathstack;

        $this->contentTypes = array(
            '.css' => 'text/css',
            '.js'  => 'application/javascript'
        );

        $this->engines        = new EngineRegistry;
        $this->preProcessors  = new ProcessorRegistry;
        $this->postProcessors = new ProcessorRegistry;

        // Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');

        // Register default Template Engines
        foreach (Template::getEngines()->getEngines() as $ext => $engine) {
            $this->registerEngine($engine, $ext);
        }
    }

    function getPreProcessors($contentType = null)
    {
        if (null === $contentType) {
            return $this->preProcessors;
        }
        return $this->preProcessors->get($contentType);
    }

    function getPostProcessors($contentType = null)
    {
        if (null === $contentType)
        {
            return $this->postProcessors;
        }
        return $this->postProcessors->get($contentType);
    }

    function registerEngine($engine, $extension)
    {
        $this->engines->register($engine, $extension);
        return $this;
    }

    function registerPreProcessor($mimeType, $processor)
    {
        $this->preProcessors->register($mimeType, $processor);
        return $this;
    }

    function registerPostProcessor($mimeType, $processor)
    {
        $this->postProcessors->register($mimeType, $processor);
        return $this;
    }

    function addLoadPath($path)
    {
        $this->loadPaths->push($path);
    }

    /**
     * Finds an Asset in the load paths or creates one from 
     * an absolute path
     *
     * @return array|Asset
     */
    function find($logicalPath)
    {
        $path = new Pathname($logicalPath);

        if ($path->isAbsolute()) {
            return new Asset($this, $path->toString(), $path->toString());
        }

        $realPath = $this->loadPaths->find($logicalPath);
        return new Asset($this, $realPath, $logicalPath);
    }

    /**
     * Sugar for find()
     *
     * @alias find()
     */
    function offsetGet($offset)
    {
        return $this->find($offset);
    }

    function offsetSet($offset, $value)
    {}

    function offsetExists($offset)
    {}

    function offsetUnset($offset)
    {}

    protected function getFinder()
    {
        $loadPaths = iterator_to_array($this->loadPaths->getIterator());
        return Finder::create()->in($loadPaths)->files();
    }
}
