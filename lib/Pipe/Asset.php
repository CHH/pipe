<?php

namespace Pipe;

class Asset
{
    protected $file;

    function __construct($file)
    {
        $this->file = $file;
    }
}
