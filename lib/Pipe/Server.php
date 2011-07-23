<?php

namespace Pipe;

use Pipe\Util\Pathname,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class Server
{
    /**
     * @var Environment
     */
    protected $environment;

    function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    function dispatch(Request $request)
    {
	$path    = ltrim($request->getRequestUri(), '/');
	$context = new Context($this->environment);
	$asset   = $this->environment[$path];

	$context->push($context->process($asset));

	$response = new Response($context->getConcatenation());
	$extensions = $asset->getExtensions();

	$contentType = $this->environment->getMimeType(array_shift($extensions));
	$response->headers->set('Content-Type', $contentType);

	return $response;
    }
}
