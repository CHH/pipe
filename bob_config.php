<?php

namespace Bob;

task('test', array('phpunit.xml'), function() {
    sh('phpunit tests/');
});

copyTask("phpunit.dist.xml", "phpunit.xml");

