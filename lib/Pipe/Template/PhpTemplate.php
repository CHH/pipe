<?php

namespace Pipe\Template;

class PhpTemplate extends Base
{
    protected function prepare()
    {
    }

    protected function evaluate($context, $vars = null)
    {
        list($templateClass, $source) = $this->getPrecompiled();
        
        eval($source);

        $preCompiled = new $templateClass;
        return $preCompiled->render($context, $vars);
    }

    protected function getPrecompiled()
    {
        $tokens = token_get_all($this->data);
        $compiled = '';

        for ($i = 0; $i < sizeof($tokens); $i++) {
            if (!is_array($tokens[$i])) {
                $compiled .= $tokens[$i];
                continue;
            }

            list($token, $content) = $tokens[$i];

            switch ($token) {
                case T_OPEN_TAG_WITH_ECHO:
                    $compiled .= 'echo';
                    break;

                case T_OPEN_TAG:
                case T_CLOSE_TAG:
                    break;

                case T_INLINE_HTML:
                    $compiled .= "echo '$content';";
                    break;

                default:
                    $compiled .= $content;
                    break;
            }
        }

        $template  = $this->getTemplateTemplate();
        $id        = md5($this->data);
        $className = "\\Pipe\\Template\\PhpTemplate\\Template_$id";

        return array($className, sprintf($template, $id, $compiled));
    }

    protected function getTemplateTemplate()
    {
        $template = <<<'TEMPLATE'
namespace Pipe\Template\PhpTemplate;

use Pipe\Template\PhpTemplateContext;

class Template_%s extends PhpTemplateContext
{
    function evaluate()
    {
        %s
    }
}
TEMPLATE;

        return $template;
    }
}

abstract class PhpTemplateContext
{
    /**
     * Template Context, all method calls get forwarded
     * to this object
     *
     * @var object
     */
    protected $context;

    function render($context = null, $vars = null)
    {
        if (null === $context) {
            $context = new \StdClass;
        }

        $this->context = $context;

        if (null !== $vars) {
            $vars = (array) $vars;

            foreach ($vars as $field => $value) {
                $this->$field = $value;
            }
        }

        ob_start();
        
        $this->evaluate();

        $content = ob_get_clean();
        return $content;
    }

    /**
     * Here goes the compiled template code
     * @returns string
     */
    abstract function evaluate();

    function __call($method, array $argv)
    {
        if (!is_callable(array($this->context, $method))) {
            throw new \BadMethodCallException("Call to undefined method $method");
        }
        return call_user_func_array(array($this->context, $method), $argv);
    }
}
