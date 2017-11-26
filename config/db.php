<?php

$local = __DIR__ . '/db-local.php';
if(file_exists($local) && is_file($local))
    return require($local);

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
