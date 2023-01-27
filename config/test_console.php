<?php

// For some reason can't call this var $db
$test_db = require __DIR__.'/test_db.php';
$console = require __DIR__.'/console.php';
$console['components']['db'] = $test_db;
$console['id'] = 'basic-tests-console';

return $console;
