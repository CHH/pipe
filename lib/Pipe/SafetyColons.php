<?php

namespace Pipe;

class SafetyColons extends \MetaTemplate\Template\Base
{
    function render($context = null, $locals = array())
    {
        # Leave the contents alone, if the data is either empty or 
        # already contains a semicolon at the end.
        if (preg_match('/\A\s*\Z/m', $this->data) or preg_match('/;\s*\Z/m', $this->data)) {
            return $this->data;
        } else {
            return "{$this->data};";
        }
    }
}
