<?php

namespace Bob\BuildConfig;

use Pipe\Config;

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

    $targets = $config->precompile;
    $manifest = new \StdClass;

    foreach ($targets as $t) {
        $asset = env()->find("$t", array("bundled" => true));

        if (!$asset) {
            println("Asset '$t' not found!", STDERR);
            exit(1);
        }

        println("Dumping '$t' as '{$asset->getDigestName()}'");
        $asset->write(array("dir" => $targetDir, "include_digest" => true));

        $manifest->{$asset->logicalPath} = $asset->getDigestName();
    }

    @file_put_contents("$targetDir/manifest.json", json_encode($manifest));
});

