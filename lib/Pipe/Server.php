<?php

namespace Pipe;

use Symfony\Component\HttpFoundation,
    DateTime;

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

    function dispatch(HttpFoundation\Request $request)
    {
        $path  = ltrim($request->getRequestUri(), '/');
        $asset = $this->environment[$path];

        if (!$asset or $path == '') {
            return $this->renderNotFound($request);
        }

        $response = new HttpFoundation\Response;
        $modifiedSince = $request->headers->get('If-Modified-Since');

        $lastModified = new \DateTime;
        $lastModified->setTimestamp($asset->getLastModified());
        $lastModified->setTimezone(new \DateTimeZone("UTC"));

        if ($modifiedSince == $lastModified->format(\DateTime::RFC1123)) {
            $response->setNotModified();
            return $response;
        }

        $response->setContent($asset->getBody());
        $response->headers->set('Content-Type', $asset->getContentType());
        $response->headers->set('Last-Modified', $lastModified->format(\DateTime::RFC1123));

        return $response;
    }

    protected function renderNotFound($request)
    {
        $response = new HttpFoundation\Response;

        ob_start();
        include(__DIR__ . "/res/404.html");
        $response->setContent(ob_get_clean());
        $response->setStatusCode(404);

        return $response;
    }
}
