<?php

namespace Pipe\Test;

use Pipe\Environment,
    Pipe\Server,
    Symfony\Component\HttpFoundation\Request;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $env = new Environment;
        $env->addLoadPath(__DIR__ . "/fixtures/directive_processor");

        $server = new Server($env);
        
        $request = Request::create('application.js');
        $response = $server->dispatch($request);

        echo $response;
    }
}
