<?php

namespace Pipe;

use Pipe\Util\PathStack,
    Pipe\Util\Pathname,
    Symfony\Component\Finder\Finder;

class Environment implements \ArrayAccess
{
    /**
     * @var PathStack
     */
    protected $loadPaths;

    /**
     * Mapping of extension to mime type
     * @var array
     */
    protected $mimeTypes = array(
        'js' => 'application/javascript',
        'css' => 'text/css'
    );

    /**
     * Holds the processors for each mimeType
     */
    protected $processors = array();

    /**
     * Processors, which are run indepently of content type before
     * the content-specific processor is run.
     */
    protected $preProcessors = array();

    function __construct()
    {
        $this->loadPaths = new PathStack;

        // Register default processors
        $this->registerPreProcessor('\\Pipe\\DirectiveProcessor');
    }

    function getPreProcessors()
    {
        return $this->preProcessors;
    }

    function getProcessorsForMimeType($mimeType)
    {
        if (empty($this->processors[$mimeType])) {
            return array();
        }
        return $this->processors[$mimeType];
    }

    function registerPreProcessor($processor)
    {
        if (!class_exists($processor)) {
            throw new \InvalidArgumentException("Class $processor is not defined");
        }

        if (!is_subclass_of($processor, "\\Pipe\\Template")) {
            throw new \InvalidArgumentException(sprintf(
                "A Processor must be a subclass of \\Pipe\\Template, subclass 
                of %s given",
                get_parent_class($processor)
            ));
        }

        $this->preProcessors[] = $processor;
        return $this;
    }

    function registerProcessor($mimeType, $processor)
    {
        if (!class_exists($processor)) {
            throw new \InvalidArgumentException("Class $processor is not defined");
        }

        if (!is_subclass_of($processor, "\\Pipe\\Template")) {
            throw new \InvalidArgumentException(sprintf(
                "A Processor must be a subclass of \\Pipe\\Template, subclass 
                of %s given",
                get_parent_class($processor)
            ));
        }

        if (!is_array($this->processors[$mimeType])) {
            $this->processors[$mimeType] = array();
        }

        $this->processors[$mimeType][] = $processor;
        return $this;
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

    function registerMimeType($extension, $mimeType)
    {
        $extension = Pathname::normalizeExtension($extension);
        $this->mimeTypes[$extension] = $mimeType;
        return $this;
    }

    function getMimeType($extension)
    {
        $extension = Pathname::normalizeExtension($extension);

        if (!$this->hasMimeType($extension)) {
            return "appliction/octet-stream";
        }
        return $this->mimeTypes[$extension];
    }

    function hasMimeType($extension)
    {
        return isset($this->mimeTypes[Pathname::normalizeExtension($extension)]);
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
