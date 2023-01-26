<?php

$db = require __DIR__.'/test_db.php';
$console = require __DIR__.'/console.php';

// Application configuration shared by all test types
return array_merge(
    $console,
    [
        'components' => [
            'db' => $db,
        ],
    ],
    [
        'id' => 'basic-tests-console',
    ]
);
