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

        $builder = new ProcessBuilder(array($finder->find('yuglify')));
        $builder->add('--terminal')
                ->add('--type')->add($type);

        $process = $builder->getProcess();
        $process->setStdin($data);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \UnexpectedArgumentException(sprintf(
                'Error while compressing "%s": %s', $this->source, $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }
}
