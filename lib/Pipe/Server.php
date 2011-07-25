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
        $path  = ltrim($request->getRequestUri(), '/');
        $asset = $this->environment[$path];

        $lastModified = new \DateTime;
        $lastModified->setTimestamp($asset->getLastModified());
        $lastModified->setTimezone(new \DateTimeZone("UTC"));

        $response = new Response($asset->getBody());
        $response->headers->set('Content-Type', $asset->getContentType());
        $response->headers->set('Last-Modified', $lastModified->format(\DateTime::RFC1123));

        return $response;
    }
}
