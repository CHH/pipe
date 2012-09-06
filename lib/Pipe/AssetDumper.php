<?php

namespace Pipe;

class AssetDumper
{
    protected $assets = array();
    protected $dir;
    protected $compress = true;

    function __construct($dir, $compress = true)
    {
        $this->dir = $dir;
        $this->compress = $compress;
    }

    function add($asset)
    {
        $this->assets[$asset->logicalPath] = $asset;
    }

    function dump()
    {
        if (!is_dir($this->dir)) {
            if (!mkdir($this->dir, 0777, true)) {
                throw new \UnexpectedValueException(sprintf(
                    "Could not create directory '%s'", $this->dir
                ));
            }
        }

        $manifest = new Manifest;

        foreach ($this->assets as $logicalPath => $asset) {
            $asset->write(array(
                "dir" => $this->dir,
                "compress" => false,
                "include_digest" => true
            ));

            if ($this->compress) {
                $asset->write(array(
                    "dir" => $this->dir,
                    "compress" => true,
                    "include_digest" => true
                ));
            }

            $manifest->add($asset);
        }

        @file_put_contents("{$this->dir}/manifest.json", $manifest->toJSON());
    }
}
