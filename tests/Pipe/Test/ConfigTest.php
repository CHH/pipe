<?php

namespace Pipe\Test;

use Pipe\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    function testCreateEnvironmentWithCompressors()
    {
        $config = new Config(array(
            'js_compressor' => 'uglify_js',
            'css_compressor' => 'yuglify_css'
        ));

        $env = $config->createEnvironment();

        $this->assertTrue($env->bundleProcessors->isRegistered(
            $env->contentType('.js'), $env->compressors['uglify_js']
        ));

        $this->assertTrue($env->bundleProcessors->isRegistered(
            $env->contentType('.css'), $env->compressors['yuglify_css']
        ));
    }
}
