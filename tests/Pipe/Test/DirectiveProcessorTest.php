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
        $this->env->appendPath(__DIR__.'/fixtures/directive_processor');
    }

    function testDependsOnInfluencesLastModified()
    {
        $asset = $this->env->find('application.js');

        $time = time();
        touch(__DIR__.'/fixtures/directive_processor/module/some_other_dep.js', $time);

        $this->assertEquals($time, $asset->getLastModified());
    }

    function testAssemblesJavascript()
    {
        $asset = $this->env->find('application.js');

        $asserted = <<<'EOL'
// util.js
// Some Utils

;
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

;// The Main Manifest
//
;
EOL;

        $this->assertEquals($asserted, $asset->getBody());
    }

    function testAssemblesStylesheets()
    {
        $asset = $this->env->find('application.css');

        $asserted = <<<'EOL'
/* util.css
 * Some Utils
 */



/* module/ui/base.css */


.ui {}

/* module/ui/color.css */


.ui .color_picker {}

/* module/ui/datepicker.css */


.ui .date_picker {}

/* ui.css
 * Some UI Components */


/* The Main Manifest
 * */


EOL;
    
        $myFile = "out";
        $fh = fopen($myFile, 'w') or die("can't open file");
        fwrite($fh, $asset->getBody());
        fclose($fh);
        $this->assertEquals($asserted, $asset->getBody());
    }


}
