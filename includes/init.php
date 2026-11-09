<?php
// Load this file at the top of every page

// Start the session (needed for CSRF token, cart, admin login)
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// Use Japan time for dates and times
date_default_timezone_set('Asia/Tokyo');

// Load settings (DB name, BASE_URL)
$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    exit('config/config.php not found. Copy config.sample.php to config.php.');
}
require_once $configFile;

// Load our own functions
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';