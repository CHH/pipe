<?php

namespace Pipe;

class ImportProcessor extends \MetaTemplate\Template\Base
{
    function render($context = null, $vars = array())
    {
        $pattern = '/^\s*@import\s+(?:"(.+)"|url\((.+)\));/';

        $data = preg_replace_callback($pattern, function($matches) use ($context) {
            if (!empty($matches[1])) {
                $path = $matches[1];
            } else if (!empty($matches[2])) {
                $path = $matches[2];
            }

            $resolvedPath = $context->resolve($path);

            if (!$resolvedPath and !($resolvedPath = $context->resolve("./$path"))) {
                throw new \UnexpectedValueException(
                    "Could not import '$path'. Not found."
                );
            }

            $asset = $context->environment->find($resolvedPath);

            if (is_callable(array($asset, "getProcessors"))) {
                $processors = $asset->getProcessors();
            } else {
                $processors = array();
            }

            $context->dependOn($resolvedPath);

            return $context->evaluate($resolvedPath, array(
                "processors" => $processors
            ));
        }, $this->data);

        return $data;
    }
}
