<?php

namespace Pipe\Test;

use Pipe\ImportProcessor,
    Pipe\Context,
    Pipe\Environment;

class ImportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    function test($file, $output)
    {
        $env = new Environment;
        $env->appendPath(__DIR__ . "/fixtures/import_processor");

        $p = new ImportProcessor(__DIR__ . "/fixtures/import_processor/$file");
        $ctx = new Context($env);

        $this->assertEquals($output, $p->render($ctx));
    }

    function dataProvider()
    {
        return array(
            array(
                "screen1.css",
                "body { font-size: 0.625em; }\n\n"
            ),
            array(
                "screen2.css",
                "body { font-size: 0.625em; }\n\n"
            )
        );
    }
}
