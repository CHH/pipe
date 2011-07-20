<?php

namespace Pipe\Util;

class PathStack extends \SplStack
{
    function __construct(array $paths = array())
    {
        foreach ($paths as $path) {
            $this->push($path);
        }
    }

    function unshift($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Path $path does not exist");
        }
        parent::unshift($path);
    }

    function push($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Path $path does not exist");
        }
        parent::push($path);
    }

    function find($subPath)
    {
        foreach ($this as $path) {
            $pathToFind = $path . DIRECTORY_SEPARATOR . $subPath;

            if (file_exists($pathToFind)) {
                return $pathToFind;
            }
        }
        return false;
    }
}
