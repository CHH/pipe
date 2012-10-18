<?php

namespace Pipe\Compressor;

class YuglifyCss extends BaseYuglifyCompressor
{
    static function getDefaultContentType()
    {
        return "text/css";
    }

    function render($context = null, $vars = array())
    {
        return $this->compress($this->getData(), self::TYPE_CSS);
    }
}
