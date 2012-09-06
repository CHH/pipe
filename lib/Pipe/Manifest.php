<?php

namespace Pipe;

class Manifest
{
    protected $manifest;

    function __construct()
    {
        $this->manifest = new \StdClass;
    }

    function add($asset)
    {
        $this->manifest->{$asset->logicalPath} = $asset->getDigestName();
        return $this;
    }

    function toJSON()
    {
        return json_encode($this->manifest);
    }
}
