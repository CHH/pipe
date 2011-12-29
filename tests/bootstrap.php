<?php

require __DIR__.'/../vendor/.composer/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader.php;

$classLoader = new UniversalClassLoader;

$classLoader->registerNamespace('Pipe', realpath(__DIR__.'/../lib'));

$classLoader->register();
