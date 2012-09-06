<?php

namespace Bob\BuildConfig;

use Pipe\Config,
    Pipe\AssetDumper;

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
    $dumper = new AssetDumper($targetDir);

    foreach ($config->precompile as $logicalPath) {
        $asset = env()->find("$t", array("bundled" => true));

        if (!$asset) {
            println("Asset '$t' not found!", STDERR);
            exit(1);
        }

        $dumper->add($asset);
    }

    $dumper->dump();
});

