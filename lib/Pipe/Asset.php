<?php

namespace Pipe;

class Asset
{
    protected $environment;

    /**
     * @var SplFileInfo
     */
    protected $file;

    /**
     * List of the file's extensions
     * @var array
     */
    protected $extensions;

    function __construct(Environment $environment, $file)
    {
        $this->environment = $environment;
        $this->file = $file;
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
        return pathinfo($this->file, PATHINFO_BASENAME);
    }
}
