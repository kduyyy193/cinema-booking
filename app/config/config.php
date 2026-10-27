<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__, 2));

return [
    'app_name'         => 'CineBooking',
    'timezone'         => 'Asia/Ho_Chi_Minh',
    'db_path'          => BASE_PATH . '/storage/cinema.sqlite',
    'schema_path'      => BASE_PATH . '/database/schema.sql',
    'log_path'         => BASE_PATH . '/storage/logs/app.log',
    'webhook_secret'   => 'demo-secret-change-me',
    'webhook_base_url' => null,
];
