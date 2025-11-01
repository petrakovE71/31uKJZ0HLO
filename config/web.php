<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'storyvalut',
    'name' => 'StoryVault',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'mz9UhMUbXkH6BUd1QwS_tEyuW8uI6Bqp',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default (for debugging as per requirements)
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                // Main application log - errors and warnings
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',
                    'maxFileSize' => 1024 * 5, // 5MB
                    'maxLogFiles' => 5,
                    'logVars' => ['_GET', '_POST'],
                ],

                // Database errors - separate file for DB issues
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'categories' => [
                        'app\repositories\*',
                        'yii\db\*',
                    ],
                    'logFile' => '@runtime/logs/database.log',
                    'maxFileSize' => 1024 * 5, // 5MB
                    'maxLogFiles' => 5,
                ],

                // Service layer logs - business logic errors and info
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => [
                        'app\services\*',
                    ],
                    'logFile' => '@runtime/logs/services.log',
                    'maxFileSize' => 1024 * 5, // 5MB
                    'maxLogFiles' => 5,
                ],

                // Email notification logs
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => [
                        'app\services\EmailService',
                    ],
                    'logFile' => '@runtime/logs/email.log',
                    'maxFileSize' => 1024 * 2, // 2MB
                    'maxLogFiles' => 3,
                ],

                // Development info log - all info messages in debug mode
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'logFile' => '@runtime/logs/info.log',
                    'maxFileSize' => 1024 * 5, // 5MB
                    'maxLogFiles' => 3,
                    'enabled' => YII_DEBUG,
                ],
            ],
        ],
        'db' => $db,
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'container' => [
        'definitions' => [
            // PostManagementService dependency injection
            'app\services\PostManagementService' => [
                'class' => 'app\services\PostManagementService',
            ],
        ],
        'singletons' => [
            // Controllers get fresh service instance for each request
            'app\controllers\PostController' => function ($container, $params, $config) {
                $config['postManagementService'] = $container->get('app\services\PostManagementService');
                return new \app\controllers\PostController($params[0], $params[1], $config);
            },
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
