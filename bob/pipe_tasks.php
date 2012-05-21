<?php

namespace Bob;

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
    $targetDir = @$_ENV["TARGET_DIR"] ?: "htdocs/assets";

    $env = getEnvironment();
    $config = getConfig();

    $manifests = $config["manifests"];

    foreach ($manifests as $manifest) {
        $asset = $env->find("$manifest", true);

        if (!$asset) {
            println("Asset '$manifest' not found!", STDERR);
            exit(1);
        }

        println("Dumping '$manifest' as '{$asset->getTargetName()}'");
        $asset->write($targetDir, $config["include_digests"] ?: false);
    }
});

