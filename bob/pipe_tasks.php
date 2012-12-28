<?php

namespace Bob\BuildConfig;

use Pipe\Config,
    Pipe\Manifest;

function config()
{
    static $config;
    return $config ?: $config = Config::fromYaml("pipe_config.yml");
}

function env()
{
    static $env;
    return $env ?: $env = config()->createEnvironment();
}

desc("Dumps all assets.");
task("assets:dump", function() {
    $config = config();
    $targetDir = @$_ENV["TARGET_DIR"] ?: $config->precompilePrefix;

    $manifest = new Manifest(env(), "$targetDir/manifest.json", $targetDir);
    $manifest->compress = true;

    $manifest->compile($config->precompile);
});

