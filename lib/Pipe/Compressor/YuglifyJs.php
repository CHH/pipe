<?php

namespace Pipe\Compressor;

class YuglifyJs extends BaseYuglifyCompressor
{
    static function getDefaultContentType()
    {
        return "application/javascript";
    }

    function render($context = null, $vars = array())
    {
        return $this->compress($this->getData(), self::TYPE_JS);
    }
}
