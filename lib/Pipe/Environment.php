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
    protected $mimeTypes;

    function __construct()
    {
        $this->loadPaths = new PathStack;
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

        $finder = $this->getFinder()->name($path->toString());

        if (1 === iterator_count($finder)) {
            $file = current($finder);
            return new Asset($this, (string) $file);
        }

        $self = $this;

        return array_map(
            function($file) use ($self) { return new Asset($self, (string) $file); },
            iterator_to_array($finder)
        );
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
            throw new \OutOfBoundsException(
                "No MIME Type registered for extension \"$extension\""
            );
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
