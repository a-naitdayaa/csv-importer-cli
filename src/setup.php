#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Setting up the database connection...\n";

try {
    $db = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']}",
        $_ENV['DB_ROOT_USER'],
        $_ENV['DB_ROOT_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $db->exec("CREATE DATABASE IF NOT EXISTS `{$_ENV['DB_NAME']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created\n";

    $db->exec("CREATE USER IF NOT EXISTS '{$_ENV['DB_USER']}'@'localhost' IDENTIFIED BY '{$_ENV['DB_PASS']}'");
    $db->exec("GRANT ALL PRIVILEGES ON `{$_ENV['DB_NAME']}`.* TO '{$_ENV['DB_USER']}'@'localhost'");
    $db->exec("FLUSH PRIVILEGES");
    echo "User created and privileges granted\n";

    $db->exec("USE `{$_ENV['DB_NAME']}`");

    $db->exec(
        "CREATE TABLE CUSTOMERS (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        customer_id VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        company VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        Phone1 VARCHAR(30) NOT NULL,
        Phone2 VARCHAR(30) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        subscription_date DATE NOT NULL,
        website VARCHAR(255) NOT NULL )"
    );
    echo "Database `{$_ENV['DB_NAME']}` is set up and ready to use.\n";
    echo "The execution time is " . (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) . " seconds.\n";
} catch (PDOException $e) {
    echo ("Failed to setup the database  " . $e->getMessage());
} catch (Exception $e) {
    echo ("an error occured " . $e->getMessage());
}
