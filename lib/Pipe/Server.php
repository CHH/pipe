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
        $path  = ltrim($request->getPathInfo(), '/');
        $asset = $this->environment->find($path, true);
        $debug = $request->query->get("debug", false);

        if (!$asset or $path == '') {
            return $this->renderNotFound($request);
        }

        if ($debug) {
            $this->environment->getBundleProcessors()->clear();
        }

        $lastModified = new \DateTime();
        $lastModified->setTimestamp($asset->getLastModified());

        $response = new HttpFoundation\Response;
        $response->setPublic();
        $response->setLastModified($lastModified);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->setContent($asset->getBody());
        $response->headers->set('Content-Type', $asset->getContentType());

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
