<?php

namespace Pipe;

class Manifest
{
    public $compress = false;

    public $files;
    public $assets;

    protected $environment;
    protected $directory;

    # Path to manifest file
    protected $manifest;

    function __construct(Environment $env, $manifest, $dir = '')
    {
        $this->files = new \StdClass;
        $this->assets = new \StdClass;

        $this->environment = $env;

        if (empty($dir)) {
            $this->directory = dirname($manifest);
        } else {
            $this->directory = $dir;
        }

        $this->manifest = $manifest;
    }

    function read()
    {
        if (!realpath($this->manifest)) {
            throw new \UnexpectedValueException(sprintf(
                'Manifest file "%s" not found', $this->manifest
            ));
        }

        $m = json_decode(file_get_contents($this->manifest));

        if (isset($m->files)) {
            foreach ($m->files as $file => $info) {
                $this->files->{$file} = $info;
            }
        }

        if (isset($m->assets)) {
            foreach ($m->assets as $logicalPath => $digestName) {
                $this->assets->{$logicalPath} = $digestName;
            }
        }
    }

    # Compiles one or more assets (by logical path) and writes the manifest.
    #
    # Example
    #
    #   $manifest = new Manifest($env, 'manifest.json', '.');
    #   $manifest->compile('boo.js');
    #   $manifest->compile(array('foo.js', 'bar.js'));
    #
    function compile($assets)
    {
        $env = $this->environment;

        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0700, true);
        }

        $assets = array_filter(array_map(function($path) use ($env) {
            return $env->find($path, array('bundled' => true));
        }, (array) $assets));

        foreach ($assets as $asset) {
            $this->files->{$asset->getDigestName()} = array(
                'logical_path' => $asset->getLogicalPath(),
                'mtime'        => date(DATE_ISO8601, $asset->getLastModified()),
                'size'         => strlen($asset->getBody()),
                'digest'       => $asset->getDigest(),
                'content_type' => $asset->getContentType()
            );

            $this->assets->{$asset->getLogicalPath()} = $asset->getDigestName();

            $asset->write(array(
                'dir' => $this->directory,
                'compress' => false,
                'include_digest' => true
            ));

            if ($this->compress) {
                $asset->write(array(
                    "dir" => $this->directory,
                    "compress" => true,
                    "include_digest" => true
                ));
            }

            $this->save();
        }
    }

    function save()
    {
        return file_put_contents($this->manifest, $this->toJSON());
    }

    function toJSON()
    {
        return json_encode(array(
            'assets' => $this->assets,
            'files' => $this->files,
        ));
    }
}

