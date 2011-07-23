<?php

$includePaths = array(
    realpath(__DIR__ . "/../lib"),
    realpath(__DIR__ . "/../vendor")
);

spl_autoload_register(function($class) use ($includePaths) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    foreach ($includePaths as $path) {
        $filePath = $path . DIRECTORY_SEPARATOR . $file;
        if (is_file($filePath) and is_readable($filePath)) {
            break;
        }
        $filePath = null;
    }

    if (empty($filePath)) {
        return false;
    }

    require $filePath;
});
