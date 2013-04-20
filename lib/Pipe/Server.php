<?php

namespace Pipe;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use DateTime;
use Psr\Log;

class Server implements HttpKernelInterface
{
    /**
     * @var Environment
     */
    protected $environment;
    protected $log;

    function __construct(Environment $environment, Log\LoggerInterface $logger = null)
    {
        if (null === $logger) {
            $logger = new Log\NullLogger;
        }

        $this->log = $logger;
        $this->environment = $environment;
    }

    function handle(HttpFoundation\Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $path  = ltrim($request->getPathInfo(), '/');
        $asset = $this->environment->find($path, array("bundled" => true));
        $debug = $request->query->get("debug", false);
        $cache = !$request->query->get("nocache", false);

        if (!$asset or $path == '') {
            return $this->renderNotFound($request);
        }

        if ($debug) {
            $this->environment->bundleProcessors->clear();
        }

        $lastModified = new \DateTime();
        $lastModified->setTimestamp($asset->getLastModified());

        $response = new HttpFoundation\Response;
        $response->setPublic();
        $response->setLastModified($lastModified);

        if ($cache and $response->isNotModified($request)) {
            return $response;
        }

        $response->setContent($asset->getBody());
        $response->headers->set('Content-Type', $asset->getContentType());
        $response->prepare($request);

        return $response;
    }

    function dispatch(HttpFoundation\Request $request)
    {
        return $this->handle($request);
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
