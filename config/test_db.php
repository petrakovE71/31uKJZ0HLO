<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = getenv('DB_DSN_TEST') ?: 'mysql:host=mysql;dbname=storyvalut_test';

return $db;
