<?php

namespace Bob\BuildConfig;

register(new \Pipe\Bob\PipeTaskLibrary);

task('default', array('test'));

task('test', array('phpunit.xml'), function() {
    sh('phpunit tests/');
});

copyTask("phpunit.dist.xml", "phpunit.xml");

