<?php

// namespace App;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('_CONFIG.php');

try {
    $dbh = new PDO("mysql:host=" . CC_DBHOST . ";dbname=" . CC_DATABASE_NAME . ";charset=utf8mb4", CC_DBUSERNAME, CC_DBPASSWD);
} catch (PDOException $e) {
    prd();
    //400 is sent to trigger an error for ajax requests.
    header('HTTP/1.1 400 Bad Request');

    echo $e->getMessage();
}


require_once __DIR__ . "/vendor/autoload.php";

// set_include_path(__DIR__ . "/app/"); // Vajadzīgs, lai nostrādātu AutoLoad pēc names

require_once __DIR__ . "/app/Casefile.php";
require_once __DIR__ . "/app/Libraries/Twig.php";
// require_once __DIR__ . "/app/Libraries/Twig.php";
require_once __DIR__ . "/Classes/indeed-development.php";
