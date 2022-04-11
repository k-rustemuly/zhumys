<?php

// Configure defaults for the whole application.

// Error reporting
error_reporting(1);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Asia/Almaty');

// Settings
$settings = [];

// Path settings
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';
$settings['template'] = $settings['root'] . '/templates';
$settings['geoip'] = $settings['root'] . '/geoip';

$settings['uploads_public_dir'] = 'uploads/files/';
$settings['uploads_dir'] = $settings['public'] . '/uploads/files/';

// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => $_ENV["API_IS_DEBUG"],
    // Should be set to false for unit tests
    'log_errors' => $_ENV["API_IS_DEBUG"],
    // Display error details in error log
    'log_error_details' => $_ENV["API_IS_DEBUG"],
];

// Logger settings
$settings['logger'] = [
    'name' => 'app',
    'path' => $settings['root'] . '/logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

// Database settings
$settings['db'] = [
    'driver' => \Cake\Database\Driver\Mysql::class,
    'host' => $_ENV["DB_HOST"],
    'encoding' => 'utf8mb4',
    'username' => $_ENV["DB_USER"],
    'password' => $_ENV["DB_PASSWORD"],
    'database' => $_ENV["DB_NAME"],
    'collation' => 'utf8mb4_unicode_ci',
    // Enable identifier quoting
    'quoteIdentifiers' => true,
    // Set to null to use MySQL servers timezone
    'timezone' => "+06:00",
    // Disable meta data cache
    'cacheMetadata' => false,
    // Disable query logging
    'log' => false,
    // PDO options
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => true,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Convert numeric values to strings when fetching.
        // Since PHP 8.1 integers and floats in result sets will be returned using native PHP types.
        // This option restores the previous behavior.
        PDO::ATTR_STRINGIFY_FETCHES => true,
    ],
];

$settings['redis'] = [
    'server' => 'tcp://127.0.0.1:6379',
    'options' => [
        'prefix' => 'zhumys_qamtu:'
    ],
];

$settings['pki_domain'] = 'http://pki.edus.kz:14141';

$settings['stat_gov'] = [
    "domain" => 'https://stat.gov.kz/api/juridical/counter/api/',
    "languages" => [
        "kk",
        "ru"
    ]
];

$settings['smsc'] = [
    "login" => "login",
    "password" => "password",
    "is_post" => false,
    "is_https" => true,
    "charset" => "utf-8",
    "from" => "api@smsc.kz"
];
return $settings;
