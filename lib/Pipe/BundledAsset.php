<?php

namespace Pipe;

class BundledAsset extends ProcessedAsset
{
    function getBody()
    {
        if (null === $this->body) {
            $body = parent::getBody();

            $bundleProcessors = $this->environment->bundleProcessors->all($this->getContentType());
            $context = new Context($this->environment);

            $this->body = $context->evaluate($this->path, array(
                "processors" => $bundleProcessors,
                "data" => $body
            ));
        }

        return $this->body;
    }
}
