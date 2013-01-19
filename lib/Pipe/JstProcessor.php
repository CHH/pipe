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
        $name = json_encode($context->logicalPath);

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
