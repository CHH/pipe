<?php

namespace Pipe\Util;

# Wrapper around the pathinfo() function, provides also some additional
# path inspecting, like checking if the path is absolute.
class Pathname
{
    protected
        $originalPath = '',

        # Array returned by pathinfo().
        $pathinfo;

    static function normalizeExtension($extension)
    {
        $extension = strtolower($extension);

        if ('.' != $extension[0]) {
            $extension = ".$extension";
        }
        return $extension;
    }

    static function join($parts)
    {
        if ($parts instanceof \Iterator) {
            $parts = iterator_to_array($parts);
        }

        return join(DIRECTORY_SEPARATOR, $parts);
    }

    function __construct($path)
    {
        $this->originalPath = (string) $path;
        $this->pathinfo = pathinfo($path);
    }

    # Checks if the path is absolute.
    #
    # Returns true or false.
    function isAbsolute()
    {
        if (strlen($this->originalPath) === 0) {
            return false;
        }

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
