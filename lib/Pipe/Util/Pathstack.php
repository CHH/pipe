<?php

namespace Pipe\Util;

class Pathstack extends \SplStack
{
    function __construct(array $paths = array())
    {
        empty($paths) ?: $this->pushAll($paths);
    }

    function unshift($path)
    {
        $path = (array) $path;

        foreach ($path as $p) {
            if (!is_dir($p)) {
                throw new \InvalidArgumentException("Path $p does not exist");
            }
            parent::unshift($p);
        }
    }

    function push($path)
    {
        $path = (array) $path;

        foreach ($path as $p) {
            if (!is_dir($p)) {
                throw new \InvalidArgumentException("Path $p does not exist");
            }
            parent::push($p);
        }
    }

    # Resolves a sub path relative to the stack of paths.
    #
    # subPath - Path to find in the load paths.
    #
    # Returns the absolute path as String, or Null when the Sub Path was
    # not found.
    function find($subPath)
    {
        foreach ($this as $path) {
            $pathToFind = $path . DIRECTORY_SEPARATOR . $subPath;

            if (file_exists($pathToFind)) {
                return $pathToFind;
            }
        }
    }

    function toArray()
    {
        return iterator_to_array($this);
    }
}
