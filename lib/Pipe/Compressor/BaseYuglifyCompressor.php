<?php

namespace Pipe\Compressor;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ExecutableFinder;

abstract class BaseYuglifyCompressor extends \MetaTemplate\Template\Base
{
    const TYPE_JS = "js";
    const TYPE_CSS = "css";

    protected function compress($data, $type = self::TYPE_JS)
    {
        $finder = new ExecutableFinder;

        if (!$cmd = $finder->find('yuglify')) {
            throw new \UnexpectedValueException('"yuglify command not found in PATH."');
        }

        $builder = new ProcessBuilder(array($cmd));
        $builder->add('--terminal')
                ->add('--type')->add($type);

        $process = $builder->getProcess();
        $process->setStdin($data);

        # Deactivate process timeout, otherwise minifying large projects can time out.
        $process->setTimeout(null);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \UnexpectedValueException(sprintf(
                'Error while compressing "%s": %s', $this->source, $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }
}
