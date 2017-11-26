<?php

$local = __DIR__ . '/params-local.php';
if(file_exists($local) && is_file($local))
    return require($local);

return [
    'adminEmail' => 'admin@example.com',
];
