<?php

namespace Pipe\Test;

use Pipe\DirectiveProcessor,
    Pipe\Context,
    Pipe\Environment;

class DirectiveProcessorTest extends \PHPUnit_Framework_TestCase
{
    var $env;

    function setUp()
    {
        $this->env = new Environment;
        $this->env->addLoadPath(__DIR__.'/fixtures/directive_processor');
    }

    function testDependsOnInfluencesLastModified()
    {
        $asset = $this->env->find('application.js');

        $time = time();
        touch(__DIR__.'/fixtures/directive_processor/module/some_other_dep.js', $time);

        $this->assertEquals($time, $asset->getLastModified());
    }

    function test()
    {
        $asset = $this->env->find('application.js');
        $asserted = <<<'EOL'
// util.js
// Some Utils

// module/ui/base.js
var UI = {
    foo: "bar"
};

// module/ui/colorpicker.js
UI.ColorPicker = (function() {
})();

// module/ui/datepicker.js
UI.DatePicker = (function() {
})();

// ui.js
// Some UI Components
// The Main Manifest
//

EOL;

        $this->assertEquals($asserted, $asset->getBody());
    }
}
