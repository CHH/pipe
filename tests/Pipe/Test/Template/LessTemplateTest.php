<?php

namespace Pipe\Test\Template;

use Pipe\Template\LessTemplate,
    Pipe\Context,
    Pipe\Environment;

class LessTemplateTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $fixture = __DIR__ . "/fixtures/less/screen.less";

        if (empty($_ENV['LESS_BIN'])) {
            $this->markTestSkipped(
                'Set $_ENV[\'LESS_BIN\'] if you want to test the LessTemplate'
            );
        }

        $template = new LessTemplate($fixture, array(
            'less_bin' => $_ENV['LESS_BIN']
        ));

        $context = new Context(new Environment);

        $assert = <<<CSS
#nav-main {
  border-radius: 6px;
  -moz-border-radius: 6px;
  -webkit-border-radius: 6px;
}
#nav-sub {
  border-radius: 3px;
  -moz-border-radius: 3px;
  -webkit-border-radius: 3px;
}

CSS;

        $this->assertEquals($assert, $template->render($context));
    }
}
