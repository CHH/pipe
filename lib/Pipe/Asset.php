<?php

namespace Pipe;

abstract class Asset
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

    abstract function getContentType();

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

    function getChecksum()
    {
        return sha1($this->getBody());
    }

    function getLastModified()
    {
        return filemtime($this->path);
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

        $ext = array_search($this->getContentType(), $this->environment->contentTypes);
        $target .= $ext;

        return $target;
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
        $filename = ($directory ? "$directory/" : '') . $this->getTargetName($digestFile);

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        @file_put_contents($filename, $this->getBody());

        if ($digestFile) {
            # Write a file which includes the asset's digest, so
            # the filename can be reconstructed using the asset's name
            # and this file.
            @file_put_contents(($directory ? "$directory/" : '') . $this->getTargetName(false) . ".digest", $this->getChecksum());
        }
    }
}
