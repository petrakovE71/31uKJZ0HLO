<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DB_DSN') ?: 'mysql:host=mysql;dbname=storyvalut',
    'username' => getenv('DB_USERNAME') ?: 'storyvalut_user',
    'password' => getenv('DB_PASSWORD') ?: 'storyvalut_pass',
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
