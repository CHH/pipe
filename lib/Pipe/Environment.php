<?php

namespace Pipe;

use Pipe\Util\PathStack,
    Pipe\Util\Pathname,
    Pipe\Util\ProcessorRegistry,
    Pipe\Util\EngineRegistry,
    Pipe\Util\ContentTypeRegistry,
    Symfony\Component\Finder\Finder;

class Environment implements \ArrayAccess
{
    /**
     * @var PathStack
     */
    protected $loadPaths;

    /**
     * @var ContentTypeRegistry
     */
    protected $contentTypes;

    /**
     * Engines per file extension
     */
    protected $engines = array();

    /**
     * Processors are like engines, but are associated with
     * a specific content type and one processor can be 
     * associated with one or more content types
     */
    protected $preProcessors;
    protected $postProcessors;

    function __construct()
    {
        $this->loadPaths = new PathStack;

        $this->contentTypes = new ContentTypeRegistry(array(
            'css' => 'text/css',
            'js' => 'application/javascript'
        ));

        $this->engines        = new EngineRegistry;
        $this->preProcessors  = new ProcessorRegistry;
        $this->postProcessors = new ProcessorRegistry;

        // Register default processors
        $this->registerPreProcessor('text/css', '\\Pipe\\DirectiveProcessor');
        $this->registerPreProcessor('application/javascript', '\\Pipe\\DirectiveProcessor');

        $this->registerEngine('less', '\\Pipe\\Template\\LessTemplate');
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

    function getEngine($extension)
    {
        $extension = Pathname::normalizeExtension($extension);
        return $this->engines->get($extension);
    }

    function registerEngine($extension, $engine)
    {
        $this->engines->register($extension, $engine);
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

    function getContentTypes()
    {
        return $this->contentTypes;
    }

    /**
     * Returns the Stack of Load Paths
     *
     * @return PathStack
     */
    function getLoadPaths()
    {
        return $this->loadPaths;
    }

    function addLoadPath($path)
    {
        $this->loadPaths->push($path);
        return $this;
    }

    /**
     * Finds an Asset in the load paths or creates one from 
     * an absolute path
     *
     * @return array|Asset
     */
    function find($path)
    {
        $path = new Pathname($path);

        if ($path->isAbsolute()) {
            return new Asset($this, $path->toString());
        }

        $assetPath = $this->loadPaths->find($path);
        return new Asset($this, $assetPath);
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

    function offsetSet($offset, $value) {}
    function offsetExists($offset) {}
    function offsetUnset($offset) {}

    protected function getFinder()
    {
        $loadPaths = iterator_to_array($this->loadPaths);
        return Finder::create()->in($loadPaths)->files();
    }
}
