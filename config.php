<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get variables from environment
$dbHost = $_ENV["DB_HOST"];
$dbName = $_ENV["DB_NAME"];
$dbUser = $_ENV["DB_USER"];
$dbPass = $_ENV["DB_PASS"];
$dbPort = $_ENV["DB_PORT"] ?: 3306;

$mysqli = new mysqli($dbHost,$dbUser,$dbPass, $dbName);
