<?php

namespace Bob\BuildConfig;

task('default', array('test'));

task('test', array('phpunit.xml'), function() {
    sh('phpunit tests/');
});

copyTask("phpunit.dist.xml", "phpunit.xml");

