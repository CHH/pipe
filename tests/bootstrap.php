<?php

require __DIR__.'/../vendor/.composer/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$classLoader = new UniversalClassLoader;

$classLoader->registerNamespace('Pipe', array(
    realpath(__DIR__.'/../lib'),
    realpath(__DIR__)
));

$classLoader->register();
