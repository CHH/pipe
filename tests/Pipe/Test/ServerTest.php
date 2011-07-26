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

    function testReturnsNotModified()
    {
        $time = time();
        $fixture = __DIR__ . "/fixtures/directive_processor/module/ui/base.js";
        touch($fixture, $time);

        $request = Request::create('application.js');

        $modifiedSince = new \DateTime;
        $modifiedSince->setTimestamp($time);
        $modifiedSince->setTimezone(new \DateTimeZone("UTC"));

        $request->headers->set(
            'If-Modified-Since', $modifiedSince->format(\DateTime::RFC1123)
        );

        $response = $this->server->dispatch($request);

        $this->assertEquals(304, $response->getStatusCode());
    }
}
