<?php

namespace Bob\BuildConfig;

use Pipe\Config;

function getConfig()
{
    static $config;
    return $config ?: $config = Config::fromYaml("pipe_config.yml");
}

function getEnvironment()
{
    static $env;
    return $env ?: $env = getConfig()->createEnvironment();
}

desc("Dumps all assets.");
task("assets:dump", function() {
    $targetDir = @$_ENV["TARGET_DIR"] ?: $config->precompilePrefix;

    $env = getEnvironment();
    $config = getConfig();

    $targets = $config->precompile;
    $manifest = new \StdClass;

    foreach ($targets as $t) {
        $asset = $env->find("$t", array("bundled" => true));

        if (!$asset) {
            println("Asset '$t' not found!", STDERR);
            exit(1);
        }

        println("Dumping '$t' as '{$asset->getTargetName()}'");
        $asset->write($targetDir);

        $manifest->{$asset->logicalPath} = $asset->getTargetName();
    }

    @file_put_contents("$targetDir/manifest.json", json_encode($manifest));
});

