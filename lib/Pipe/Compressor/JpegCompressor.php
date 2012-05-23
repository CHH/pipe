<?php

namespace Pipe\Compressor;

use Imagine\Imagick\Imagine,
    Imagine\Filter\Transformation;

class JpegCompressor extends \MetaTemplate\Template\Base
{
    const DEFAULT_QUALITY = 95;

    protected $image;
    protected $transformation;

    static function getDefaultContentType()
    {
        return "image/jpeg";
    }

    function prepare()
    {
        $imagine = new Imagine;
        $this->image = $imagine->load($this->data);
    }

    function render($context = null, $vars = array())
    {
        $quality = @$this->options["quality"] ?: self::DEFAULT_QUALITY;

        $this->image->strip();
        return $this->image->get("jpeg", array("quality" => $quality));
    }
}
