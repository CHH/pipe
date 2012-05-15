<?php

namespace Pipe;

class BundledAsset extends Asset
{
    function getProcessors()
    {
        $parentProcessors = parent::getProcessors();

        return array_merge($parentProcessors, $bundleProcessors);
    }

    function getBody()
    {
        if (null === $this->body) {
            $body = parent::getBody();

            $bundleProcessors = $this->environment->bundleProcessors->get($this->getContentType());
            $context = new Context($this->environment);

            $this->body = $context->evaluate($this->path, array(
                "processors" => $bundleProcessors,
                "data" => $body
            ));
        }

        return $this->body;
    }
}
