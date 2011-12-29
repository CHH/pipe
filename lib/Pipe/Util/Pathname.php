<?php

namespace Pipe\Util;

/**
 * Wrapper around the pathinfo() function, provides also some additional 
 * path inspecting, like checking if the path is absolute 
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class Pathname
{
    protected $originalPath;

    /**
     * @var array
     */
    protected $pathinfo;

    function __construct($path)
    {
        $this->originalPath = $path;
        $this->pathinfo = pathinfo($path);
    }

    static function normalizeExtension($extension)
    {
        $extension = strtolower($extension);

        if ('.' != $extension[0]) {
            $extension = ".$extension";
        }
        return $extension;
    }

    /**
     * Checks if the pathname is an absolute path
     *
     * @return bool
     */
    function isAbsolute()
    {
        if ($this->isWindows()) {
            return
                '\\' == $this->originalPath[0] 
                or
                preg_match('/^[a-zA-Z]\:\\\\/', $this->originalPath);
        }
        return '/' == $this->originalPath[0];
    }

    protected function isWindows()
    {
        return "WIN" == strtoupper(substr(PHP_OS, 0, 3));
    }

    function getExtension()
    {
        return $this->pathinfo['extension'];
    }

    function getBasename()
    {
        return $this->pathinfo['basename'];
    }

    function getFilename()
    {
        return $this->pathinfo['filename'];
    }

    function getDirname()
    {
        return $this->pathinfo['dirname'];
    }

    function toString()
    {
        return $this->originalPath;
    }

    function __toString()
    {
        return $this->toString();
    }
}
