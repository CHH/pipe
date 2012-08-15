<?php

namespace Pipe;

use CHH\FileUtils;

abstract class Asset
{
    public
        $path,
        $logicalPath,
        $digest,

        # The asset's declared dependencies.
        $dependencies = array();

    protected
        $environment,
        $body,

        # List of the file's extensions.
        $extensions;

    # The determination of the asset's content type is up to the specific
    # implementation, and could be either derived from the file extension
    # or by looking into the file's contents.
    #
    # Returns a MIME type as String, for example "text/javascript".
    abstract function getContentType();

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

    function getBody()
    {
        if (null === $this->body) {
            $this->body = file_get_contents($this->path);
        }
        return $this->body;
    }

    function __toString()
    {
        try {
            return $this->getBody();
        } catch (\Exception $e) {
            return '';
        }
    }

    # Public: Returns the asset's basename.
    #
    # includeExtensions: Set to false to strip all extensions from the filename (default: true)
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

    function getDigest()
    {
        if (null === $this->digest) {
            $this->digest = sha1($this->getBody());
        }

        return $this->digest;
    }

    function getLastModified()
    {
        return filemtime($this->path);
    }

    function getDigestName()
    {
        $ext = $this->getFormatExtension();
        return preg_replace('/(\.\w+)$/', "-{$this->getDigest()}$ext", $this->logicalPath);
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
            $target .= '-' . $this->getDigest();
        }

        $target .= $this->getFormatExtension();

        return $target;
    }

    # Public: Writes the asset's content to the directory.
    #
    # options - Array of options.
    #           dir:            Write the asset to the given directory. (default: '')
    #           include_digest: Should the digest be spliced into the filenames? (default: false)
    #           compress:       Should the contents be GZIP compressed? (default: false)
    #
    # Returns Nothing.
    function write($options = array())
    {
        $dir = @$options["dir"];
        $compress = @$options["compress"] ?: false;
        $includeDigest = @$options["include_digest"] ?: false;

        $filename = FileUtils::join(array($dir, ($includeDigest ? $this->getDigestName() : $this->logicalPath)));

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        $body = $this->getBody();

        if ($compress) {
            $body = gzencode($body, 9);
            $filename .= ".gz";
        }

        @file_put_contents($filename, $body);
    }

    # Determines the format extension.
    #
    # The format extension is the extension which is not
    # assigned to an engine and is present in the environment's
    # configured content types.
    #
    # Example:
    #
    #   $asset = $env->find("/foo.js");
    #   echo $asset->getFormatExtension();
    #   // .js
    #
    #   $asset = $env->find("/foo.coffee.js");
    #   echo $asset->getFormatExtension();
    #   // .js
    #
    #   $asset = $env->find("/foo.coffee");
    #   echo $asset->getFormatExtension();
    #   // .js
    #
    # Returns a String.
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

        return $formatExtension
            ?: array_search($this->getEngineContentType(), $environment->contentTypes)
            ?: join('', $this->getExtensions());
    }

    # Collects the file's extensions.
    #
    # Returns an Array of normalized extensions.
    protected function getExtensions()
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
                return FileUtils::normalizeExtension($ext);
            }, $extensions);
        }

        return $this->extensions;
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

    protected function getEngines()
    {
        $env = $this->environment;

        return array_map(
            function($ext) use ($env) {
                return $env->engines->get($ext);
            },
            $this->getEngineExtensions()
        );
    }

    protected function getEngineExtensions()
    {
        $env = $this->environment;

        return array_filter(
            $this->getExtensions(),
            function($ext) use ($env) {
                return $env->engines->get($ext);
            }
        );
    }
}
