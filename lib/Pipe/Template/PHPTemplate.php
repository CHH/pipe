<?php

namespace Pipe\Template;

class PHPTemplate extends Base
{
    protected $templates = array();

    protected function evaluate($context, array $vars = array())
    {
        $template = $this->getTemplateClass($vars);
        return $template->render($context, $vars);
    }

    protected function getTemplateClass(array $locals)
    {
        $key = join(',', array_keys($locals));

        if (!isset($this->templates[$key])) {
            $this->templates[$key] = $this->compileTemplateClass($locals);
        }
        return $this->templates[$key];
    }

    protected function compileTemplateClass(array $locals)
    {
        $id = $this->getCacheId($locals);
        $templateClass = "\\Pipe\\Template\\PHPTemplate\\Template_$id";

        if (class_exists($templateClass)) {
            return;
        }

        $source = $this->getPrecompiled($locals);
        $classTemplate = $this->getTemplateTemplate();

        eval(sprintf($classTemplate, $id, $source));

        return new $templateClass;
    }

    protected function prepare()
    {
    }

    function getCacheId(array $vars = array())
    {
        return md5($this->getPreamble($vars).$this->data);
    }

    function getPrecompiled(array $vars = array())
    {
        $tokens = token_get_all($this->data);
        $compiled = '';
        $preamble = $this->getPreamble($vars);

        $compiled .= $preamble;

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
                    $content = addslashes($content);
                    $compiled .= sprintf('echo %s;', var_export($content, true));
                    break;

                case T_VARIABLE:
                    if ('$this' == $content) {
                        $content = '$this->context';
                    }
                    // break intentionally omitted

                default:
                    $compiled .= $content;
                    break;
            }
            unset($token, $content);
        }
        unset($tokens);

        return $compiled;
    }

    protected function getPreamble(array $vars = array()) 
    {
        $preamble = '';
        foreach ($vars as $var => $value) {
            $key = var_export($var, true);
            $preamble .= "\$$var = \$locals[$key];\n";
        }

        return $preamble;
    }

    protected function getTemplateTemplate()
    {
        $template = <<<'TEMPLATE'
namespace Pipe\Template\PHPTemplate;

use Pipe\Template\PHPTemplateContext;

class Template_%s extends PHPTemplateContext
{
    function evaluate(array $locals)
    {
        %s
    }
}
TEMPLATE;

        return $template;
    }
}

/**
 * The Base Class for all compiled templates.
 */
abstract class PHPTemplateContext
{
    /**
     * Template Context, all method calls get forwarded
     * to this object
     *
     * @var object
     */
    protected $context;

    function render($context = null, array $locals = array())
    {
        if (null === $context) {
            $context = new \StdClass;
        }

        $this->context = $context;

        ob_start();
        
        $this->evaluate($locals);

        $content = ob_get_clean();
        return $content;
    }

    /**
     * Here goes the compiled template code
     * @return string
     */
    abstract function evaluate(array $locals);
}
