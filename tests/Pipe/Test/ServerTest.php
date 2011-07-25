<?php

namespace Pipe\Test;

use Pipe\Environment,
    Pipe\Server,
    Symfony\Component\HttpFoundation\Request;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    protected $server;

    function setUp()
    {
        $env = new Environment;
        $env->addLoadPath(__DIR__ . "/fixtures/directive_processor");

        $this->server = new Server($env);
    }

    function testServerReturnsLastModifiedHeader()
    {
        // Modify the mtime of a file deep in the dependency graph
        $time = time();
        touch(__DIR__ . "/fixtures/directive_processor/module/ui/base.js", $time);

        $request  = Request::create('application.js');
        $response = $this->server->dispatch($request);

        // Check if the Last Modified header
        $lastModified = $response->headers->get('Last-Modified');
        $this->assertEquals($time, strtotime($lastModified));
    }

    function testServerReturnsContentType()
    {
        $request  = Request::create('application.js');
        $response = $this->server->dispatch($request);

        $this->assertEquals(
            'application/javascript', $response->headers->get('Content-Type')
        );
    }
}
