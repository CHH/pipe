<?php

namespace Pipe\Util;

class ContentTypeRegistry extends \ArrayObject
{
    function __construct(array $contentTypes = array())
    {
        foreach ($contentTypes as $ext => $contentType) {
            $this->set($ext, $contentType);
        }
    }

    function set($extension, $contentType)
    {
        $extension = Pathname::normalizeExtension($extension);
        parent::offsetSet($extension, $contentType);
        return $this;
    }

    function get($extension)
    {
        $extension = Pathname::normalizeExtension($extension);
        if (!parent::offsetExists($extension)) {
            return;
        }
        return parent::offsetGet($extension);
    }

    function getExtension($contentType)
    {
        $index = array_search($contentType, (array) $this);

        if ($index === false) {
            return;
        }
        return $this[$index];
    }

    function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    function offsetGet($offset)
    {
        return $this->get($offset);
    }

    function offsetExists($offset)
    {
        return (bool) $this->get($offset);
    }
}
