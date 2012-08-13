<?php

namespace Pipe;

# Public: This is intended as a postprocessor which adds a single colon
# at the end of the data.
#
# When Javascript code which uses the Module Pattern is concatenated,
# then there often occur parse errors, which are due to missing colons.
#
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
