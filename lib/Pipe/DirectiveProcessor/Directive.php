<?php

namespace Pipe\DirectiveProcessor;

use Pipe\Context;

interface Directive
{
    /**
     * The Directive's name
     */
    function getName();

    /**
     * Called whenever this directive is found within the source
     *
     * @param  AssetInterface $parent The file where the directive was found
     * @param  array $argv
     * @return AssetInterface|null
     */
    function execute(Context $context, array $argv);
}
