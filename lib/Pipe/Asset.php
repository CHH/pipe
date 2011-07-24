<?php

namespace Pipe;

class Asset
{
    /**
     * @var Environment
     */
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

    function process(Context $context)
    {
        $preProcessors = $this->environment->getPreProcessors();

        $content = null;
        foreach ($preProcessors as $processorClass) {
            $p = new $processorClass($this->getPath(), $content);
            $c = $p->render($context);
            $content = function() use ($c) {
                return $c;
            };
        }

        $processors = array();
        foreach ($this->getExtensions() as $ext) {
            $mimeType = $this->environment->getMimeType($ext);
            $processors = array_merge(
                $processors, 
                $this->environment->getProcessorsForMimeType($mimeType)
            );
        }

        $content = null;
        foreach ($processors as $processorClass) {
            $p = new $processorClass($this->getPath(), $content);
            $c = $p->render($context);
            $content = function() use ($c) {
                return $c;
            };
        }

        $context->push($c);
    }
}
