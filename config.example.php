<?php
/**
 * Copy this file to config.php and fill in your own values.
 * config.php is listed in .gitignore and must never be committed.
 */
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'event_checkin',
        'user' => 'your_database_user',
        'pass' => 'your_database_password',
    ],
    'staff' => [
        // Run `php bin/hash-password.php` and paste the result here.
        'password_hash' => '',
    ],
    'app' => [
        'timezone' => 'Africa/Dar_es_Salaam',
        // Full public address, used in QR codes and WhatsApp links.
        // Leave empty to detect it from the request.
        'base_url' => '',
    ],
];
