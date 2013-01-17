<?php

namespace Pipe\Bob;

use Bob\TaskLibraryInterface;
use Bob\Application;
use Bob\BuildConfig as b;

class PipeTaskLibrary implements TaskLibraryInterface
{
    function register(Application $app)
    {
        $app['pipe.environment'] = $app->share(function() use ($app) {
            return new Environment;
        });
    }

    function boot(Application $app)
    {
        $app->task('pipe:precompile', function() use ($app) {
            $targetDirectory = $app['pipe.precompile_directory'];
            $assets = (array) $app['pipe.precompile'];

            $manifest = new Manifest($app['pipe.environment'], "$targetDirectory/manifest.json", $targetDirectory);
            $manifest->setLogger($app['log']);
            $manifest->compile($assets);
        })->description = "Precompile assets";
    }
}

