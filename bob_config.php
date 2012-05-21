<?php

namespace Bob;

task('test', array('phpunit.xml'), function() {
    echo `phpunit tests/`;
});

fileTask('phpunit.xml', array('phpunit.dist.xml'), function($task) {
    copy($task->prerequisites[0], $task->name);
});
