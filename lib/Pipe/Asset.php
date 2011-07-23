<?php

namespace Pipe;

class Asset
{
    protected $environment;

    /**
     * @var SplFileInfo
     */
    protected $path;

    /**
     * List of the file's extensions
     * @var array
     */
    protected $extensions;

    function __construct(Environment $environment, $path)
    {
        $this->environment = $environment;
        $this->path = $path;
    }

    /**
     * Returns a list of the File's Extensions, in reverse order
     *
     * @return array
     */
    function getExtensions()
    {
        if (null === $this->extensions) {
            $basename = $this->getBasename();

            // Avoid treating name of a dotfile as extension by
            // ignoring dots at the first offset in the string
            if (false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $this->extensions = array_reverse(explode('.', substr($basename, $pos + 1)));
        }
        return $this->extensions;
    }

    function getBasename()
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    function getDirname()
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    function getPath()
    {
        return $this->path;
    }
}
