<?php

// use yii\queue\amqp_interop\Queue;
use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\OfferEmailer;
use yii\di\Instance;

$params = require __DIR__.'/params.php';
$db = require __DIR__.'/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        // 'queue',
    ],
    'controllerNamespace' => 'app\\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        // 'queue' => [
        // 'class' => Queue::class,
        // 'driver' => Queue::ENQUEUE_AMQP_LIB,
        // ],
        'cache' => [
            'class' => 'yii\\caching\\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\\log\\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'container' => [
        'definitions' => [
            // CliQueue::class => [
            //     'class' => Queue::class,
            //     'driver' => Queue::ENQUEUE_AMQP_LIB,
            // ],
            // AudienceInterface::class => AudienceRepository::class,
            // OfferInterface::class => [
            //     'class' => DbOffer::class,
            //     '__construct()' => [
            //         Instance::of(Client::class),
            //         $_ENV['API_URL'],
            //         $_ENV['API_KEY'],
            //     ],
            // ],
            // QueueStoreInterface::class => DbQueueStore::class,
            // MailerInterface::class => [
            //     'class' => Mailer::class,
            //     'transport' => [
            //         'dsn' => $_ENV['MAILER_TRANSPORT_DSN'],
            //     ],
            // ],
            // AnalyticsInterface::class => [
            //     'class' => Amplitude::class,
            //     '__construct()' => [
            //         Instance::of(Client::class),
            //         $_ENV['AMPLITUDE_URL'],
            //         $_ENV['AMPLITUDE_KEY'],
            //     ],
            // ],
            // MessageInterface::class => [
            //     'class' => Message::class,
            //     'from' => $_ENV['MESSAGE_FROM'],
            // ],
        ],
        'singletons' => [
            OfferEmailer::class => [
                'class' => OfferEmailer::class,
                '__construct()' => [
                    // Instance::of(AudienceInterface::class),
                    // Instance::of(MailerInterface::class),
                    // Instance::of(AnalyticsInterface::class),
                ],
            ],
        ],
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\\gii\\Module',
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\\debug\\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // 'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
