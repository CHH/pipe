<?php

namespace Pipe;

class JstProcessor extends \MetaTemplate\Template\Base
{
    static $defaultNamespace = "this.JST";

    static function getDefaultContentType()
    {
        return "application/javascript";
    }

    function render($context = null, $vars = array())
    {
        $namespace = static::$defaultNamespace;
        $logicalPath = $context->environment->logicalPath($context->path);
        $basename = substr($logicalPath, 0, strpos($logicalPath, '.'));
        $name = json_encode($basename);

        # Indent with four spaces
        $value = preg_replace('/$(.)/m', '\\1    ', $this->getData());

        if (@$this->options['quote']) {
            # Escape quotes, and quote data
            $value = sprintf('"%s"', str_replace('"', '\\"', $value));
        }

        return <<<JST
(function() {
    $namespace || ($namespace = {});

    {$namespace}[{$name}] = {$value};
}).call(this);
JST;
    }
}
