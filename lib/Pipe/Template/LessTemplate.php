<?php

namespace Pipe\Template;

use Pipe\Context,
    Symfony\Component\Process\Process;

class LessTemplate extends Base
{
    protected $outputFile;

    function __construct($file, array $options = array(), $reader = null)
    {
        $this->file = $file;
        $this->options = $options;
    }

    static function getDefaultContentType()
    {
        return "text/css";
    }

    protected function evaluate($context, $vars = null)
    {
        $lessBin = isset($options['less_bin']) 
            ? $options['less_bin'] : '/usr/local/bin/lessc';

        $compress = isset($options['compress']) 
            ? $options['compress'] : false;

        $outputFile = tempnam(sys_get_temp_dir(), 'pipe_less_output');

        $cmd = $lessBin.' '.$this->file.' '.$outputFile;

        if ($compress) {
            $cmd .= ' -x';
        }

        $process = new Process($cmd);

        $process->setEnv(array(
            'PATH' => $_SERVER['PATH']
        ));

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "lessc returned an error: " . $process->getErrorOutput()
            );
        }

        $content = file_get_contents($outputFile);
        unlink($outputFile);

        return $content;
    }
}
