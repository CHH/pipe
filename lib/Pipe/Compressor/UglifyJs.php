<?php

namespace Pipe\Compressor;

use Symfony\Component\Process\Process;

class UglifyJs extends \MetaTemplate\Template\Base
{
    static function getDefaultContentType()
    {
        return "application/javascript";
    }

    function render($context = null, $vars = array())
    {
        $cmd = "/usr/bin/env uglifyjs";

        $process = new Process($cmd);
        $process->setStdin($this->data);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(
                "uglifyjs exited with an error: {$process->getErrorOutput()}"
            );
        }

        return $process->getOutput();
    }
}
