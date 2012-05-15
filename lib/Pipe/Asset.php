<?php

namespace Pipe;

use Pipe\Environment,
    Pipe\Context,
    Pipe\Util\Pathname;

class Asset
{
    var $path;
    var $logicalPath;

    # The asset's declared dependencies.
    var $dependencies = array();

    protected $environment;
    protected $body;

    # List of the file's extensions.
    protected $extensions;

    # Initializes the asset.
    #
    # environment - The Environment object.
    # path        - The absolute path to the asset.
    # logicalPath - The path relative to the environment.
    function __construct(Environment $environment, $path, $logicalPath = null)
    {
        $this->environment = $environment;
        $this->path = $path;
        $this->logicalPath = $logicalPath;
    }

    # Processes and stores the asset's body.
    #
    # Returns the body as String.
    function getBody()
    {
        if (null === $this->body) {
            $ctx    = new Context($this->environment);
            $result = "";

            $body = $ctx->evaluate($this->path);

            $this->dependencies = array_merge($this->dependencies, $ctx->dependencyPaths);

            $result .= join("\n", $ctx->dependencyAssets);
            $result .= $body;

            $this->body = $result;
        }

        return $this->body;
    }

    # Alias for `getBody()`.
    function __toString()
    {
        return $this->getBody();
    }

    # Public: Calculates the date when this asset and its dependencies
    # were last modified.
    #
    # Returns a timestamp as Integer.
    function getLastModified()
    {
        # Load the asset, if it's not loaded
        if (null === $this->body) {
            $this->getBody();
        }

        $dependenciesLastModified = array_map("filemtime", $this->dependencies);
        return max(filemtime($this->path), max($dependenciesLastModified));
    }

    # Public: Determines the asset's content type, based on its extensions.
    #
    # Returns the content type as String, or False when the content type
    # couldn't be detected.
    function getContentType()
    {
        $formatExtension = $this->getFormatExtension();

        return isset($this->environment->contentTypes[$formatExtension])
            ? $this->environment->contentTypes[$formatExtension]
            : false;
    }

    function getFormatExtension()
    {
        $environment = $this->environment;

        $formatExtension = current(array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return
                    isset($environment->contentTypes[$ext])
                    and !$environment->engines->get($ext);
            }
        ));

        return $formatExtension ?: array_search($this->getEngineContentType(), $environment->contentTypes);
    }

    function getEngineExtensions()
    {
        $environment = $this->environment;

        return array_filter(
            $this->getExtensions(),
            function($ext) use ($environment) {
                return $environment->engines->get($ext);
            }
        );
    }

    # Collects the file's extensions.
    #
    # Returns an Array of normalized extensions.
    function getExtensions()
    {
        if (null === $this->extensions) {
            $basename = $this->getBasename();

            # Avoid treating name of a dotfile as extension by
            # ignoring dots at the first offset in the string
            if (!$basename or false === ($pos = strpos($basename, '.', 1))) {
                return array();
            }

            $extensions = explode('.', substr($basename, $pos + 1));

            $this->extensions = array_map(function($ext) {
                return Pathname::normalizeExtension($ext);
            }, $extensions);
        }

        return $this->extensions;
    }

    function getProcessors()
    {
        $contentType = $this->getContentType();

        return array_merge(
            $this->environment->preProcessors->get($contentType),
            array_reverse($this->getEngines()),
            $this->environment->postProcessors->get($contentType)
        );
    }

    function getEngines()
    {
        $env = $this->environment;

        return array_map(
            function($ext) use ($env) {
                return $env->engines->get($ext);
            },
            $this->getEngineExtensions()
        );
    }

    # Public: Writes the asset's content to the directory.
    #
    # directory  - Directory to write the asset to, optional.
    # digestFile - Additionally write a file named like the asset,
    #              but contains the asset's SHA1 hash.
    #
    # Returns Nothing.
    function write($directory = '', $digestFile = true)
    {
        $filename = $directory . '/' . $this->getTargetName();

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        @file_put_contents($filename, $this->getBody());

        if ($digestFile) {
            # Write a file which includes the asset's digest, so
            # the filename can be reconstructed using the asset's name
            # and this file.
            @file_put_contents($directory . '/' . $this->getTargetName(false) . ".digest", $this->getChecksum());
        }
    }

    # Returns the body's checksum as String.
    function getChecksum()
    {
        return sha1($this->getBody());
    }

    # Public: Returns the filename with extension, that's appropiate
    # after the asset was processed.
    #
    # includeHash - Include the SHA1 hash of the asset's body in the filename,
    #               defaults to True.
    #
    # Returns the file name as String.
    function getTargetName($includeHash = true)
    {
        $target = $this->getBasename(false);

        if ($includeHash) {
            $target .= '-' . $this->getChecksum();
        }

        $target .= $this->getFormatExtension();
        return $target;
    }

    # Public: Returns the asset's basename.
    #
    # includeExtensions: Set to false to strip all extensions from the filename,
    #                    defaults to True.
    #
    # Returns the basename as String.
    function getBasename($includeExtensions = true)
    {
        $basename = basename($this->path);

        if (!$includeExtensions) {
            $basename = substr($basename, 0, strpos($basename, '.'));
        }

        return $basename;
    }

    function getDirname()
    {
        return dirname($this->path);
    }

    protected function getEngineContentType()
    {
        foreach ($this->getEngineExtensions() as $ext) {
            $engine = $this->environment->engines->get($ext);

            if (is_callable(array($engine, "getDefaultContentType"))) {
                return $engine::getDefaultContentType();
            }
        }
    }
}
