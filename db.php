<?php

function get_db_connection(): PDO
{
    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];

    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";

    try {
        return new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
       
        throw new PDOException('Database connection failed: ' . $e->getMessage());
    }
}
