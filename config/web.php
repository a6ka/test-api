<?php

use yii\web\JsonParser;
use yii\web\JsonResponseFormatter;

$params = require __DIR__ . '/params.php';
$db     = require __DIR__ . '/db.php';

$config = [
    'id'         => 'basic',
    'basePath'   => dirname(__DIR__),
    'bootstrap'  => ['log'],
    'aliases'    => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request'    => [
            'enableCookieValidation' => false,
            'enableCsrfValidation'   => false,
            'cookieValidationKey'    => 'some_secret_key',
            'parsers'                => [
                'application/json' => JsonParser::class,
            ],
        ],
        'response'   => [
            'format'     => yii\web\Response::FORMAT_JSON,
            'charset'    => 'UTF-8',
            'formatters' => [
                'json' => [
                    'class'         => JsonResponseFormatter::class,
                    'prettyPrint'   => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'redis'      => [
            'class'    => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port'     => 6379,
            'database' => 4,
        ],
        'cache'   => [
            'class'        => 'yii\caching\MemCache',
            'useMemcached' => true,
            'servers'      => [
                'app01' =>
                    [
                        'host'   => '127.0.0.1',
                        'port'   => 11211,
                        'weight' => 100,
                    ],
            ],
        ],
        'db'         => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
            ],
        ],
    ],
    'params'     => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
