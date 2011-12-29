<?php

namespace Pipe;

use Pipe\Util\Pathname;

class Context
{
    protected $environment;

    protected $requiredPaths    = array();
    protected $dependencyPaths  = array();
    protected $dependencyAssets = array();

    protected $path;

    function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    function dependOn($path)
    {
        $this->dependencyPaths[] = $this->resolve($path);
        return $this;
    }

    function evaluate($path, array $options = array())
    {
        if (isset($options['data'])) {
            $data = $options['data'];
        } else {
            $data = @file_get_contents($path);
        }

        $asset = $this->environment->find($path);

        foreach ($asset->getProcessors() as $processor) {
			$p = new $processor($path, array(
				'reader' => function() use ($data) {
					return $data;
				}
			));

            $data = $p->render($this);
        }

        return $data;
    }

    function getDependencyAssets()
    {
        return $this->dependencyAssets;
    }

    function getDependencyPaths()
    {
        return $this->dependencyPaths;
    }

    function getRequiredPaths()
    {
        return $this->requiredPaths;
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function requireAsset($path)
    {
        $resolvedPath = $this->resolve($path);

        if (in_array($resolvedPath, $this->requiredPaths)) {
            return $this;
        }

        $this->dependOn($resolvedPath);
        $this->dependencyAssets[] = $this->evaluate($resolvedPath);
        $this->requiredPaths[] = $resolvedPath;

        return $this;
    }

    protected function resolve($path)
    {
        $pathinfo = new Pathname($path);

        if ($pathinfo->isAbsolute()) {
            return realpath($path);
        }

        $loadPaths = $this->environment->loadPaths;

        return realpath($loadPaths->find($path));
    }
}
