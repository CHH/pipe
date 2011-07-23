<?php

namespace Pipe\Test\Util;

use Pipe\Util\Pathname;

class PathnameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider windowsPathProvider
     */
    function testWindowsPaths($pathname, $expectedResult)
    {
        $builder = $this->getMockBuilder('\\Pipe\\Util\\Pathname');
        $builder->setMethods(array('isWindows'));
        $builder->setConstructorArgs(array($pathname));

        $path = $builder->getMock();
        $path->expects($this->any())
            ->method('isWindows')
            ->will($this->returnValue(true));

        $this->assertEquals($expectedResult, $path->isAbsolute());
    }

    /**
     * @dataProvider unixPathProvider
     */
    function testUnixPaths($pathname, $expectedResult)
    {
        $builder = $this->getMockBuilder('\\Pipe\\Util\\Pathname');
        $builder->setMethods(array('isWindows'));
        $builder->setConstructorArgs(array($pathname));

        $path = $builder->getMock();
        $path->expects($this->any())
            ->method('isWindows')
            ->will($this->returnValue(false));

        $this->assertEquals($expectedResult, $path->isAbsolute());
    }

    function windowsPathProvider()
    {
        return array(
            array('C:\\Windows\\System32', true),
            array('/tmp', false),
            array('\\Windows\\System32', true),
            array('\\\\foo\\bar', true)
        );
    }

    function unixPathProvider()
    {
        return array(
            array('/usr/local/bin', true),
            array('C:\\tmp', false),
            array('/', true)
        );
    }
}
