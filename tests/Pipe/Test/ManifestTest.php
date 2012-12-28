<?php

namespace Pipe\Test;

use Pipe\Manifest;
use Pipe\Environment;
use org\bovigo\vfs\vfsStream;

class ManifestTest extends \PHPUnit_Framework_TestCase
{
    function testManifestDumpsAssets()
    {
        $dir = vfsStream::setup('assets');

        $env = new Environment;
        $env->appendPath(__DIR__ . '/fixtures');

        $asset = $env->find('asset1.js', array('bundled' => true));
        $digestName = $asset->getDigestName();

        $manifest = new Manifest($env, vfsStream::url('assets') . '/manifest.json');
        $manifest->compress = true;
        $manifest->compile('asset1.js');

        $json = json_decode($manifest->toJSON(), true);

        $this->assertEquals($digestName, $json["assets"]["asset1.js"]);

        $fileInfo = $json['files'][$digestName];

        $this->assertArrayHasKey('size', $fileInfo);
        $this->assertArrayHasKey('logical_path', $fileInfo);
        $this->assertArrayHasKey('content_type', $fileInfo);
        $this->assertArrayHasKey('digest', $fileInfo);

        $this->assertTrue($dir->hasChild('manifest.json'));
        $this->assertTrue($dir->hasChild($digestName));
        $this->assertTrue($dir->hasChild($digestName . '.gz'));
    }
}

