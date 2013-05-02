<?php

namespace Pipe;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use DateTime;
use Psr\Log;

/**
 * Middleware which intercepts all requests to /_pipe and renders
 * the asset when found in the environment.
 */
class Middleware implements HttpKernelInterface
{
    protected $app;
    protected $env;
    protected $log;

    function __construct(HttpKernelInterface $app, Environment $env, Log\LoggerInterface $log = null)
    {
        $this->app = $app;

        if (null !== $this->log) {
            $this->log = $log;
        } else {
            $this->log = new Log\NullLogger;
        }

        $this->env = $env;
    }

    function handle(Request $request, $type = HttpFoundation::MASTER_REQUEST, $catch = true)
    {
        $pathinfo = $request->getPathInfo();

        if (preg_match('{^/_pipe/(.+)$}', $pathinfo, $matches)) {
            $path = $matches[1];

            if (!$path or !$asset = $this->env->find($path, array('bundled' => true))) {
                $this->log->error("pipe: Asset '$path' not found");
                return new Response('Not Found', 404);
            }

            $lastModified = new \DateTime;
            $lastModified->setTimestamp($asset->getLastModified());

            $response = new Response;
            $response->setPublic();
            $response->setLastModified($lastModified);

            if ($response->isNotModified($request)) {
                $this->log->info("pipe: 302 $path");
                return $response;
            }

            $start = microtime(true);
            $response->setContent($asset->getBody());
            $this->log->info(sprintf('pipe: Rendered "%s" in %d seconds', $path, microtime(true) - $start));

            $response->headers->set('Content-Type', $asset->getContentType());
            $response->prepare($request);

            return $response;
        }

        return $this->app->handle($request, $type, $catch);
    }
}
