<?php

$file = realpath(__DIR__ . '/database.ini');
if (!file_exists($file)) {
    $databaseUrl = parse_url($_ENV['DATABASE_URL']);
    $username = $databaseUrl['user']; // janedoe
    $password = $databaseUrl['pass']; // mypassword
    $host = $databaseUrl['host']; // localhost
    $port = $databaseUrl['port']; // 5432
    $dbName = ltrim($databaseUrl['path'], '/');
    $conStr = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
        $host,
        $port,
        $dbName,
        $username,
        $password
    );
} else {
    $params = parse_ini_file('database.ini');
    $conStr = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
        $params['host'],
        $params['port'],
        $params['database'],
        $params['user'],
        $params['password']
    );
}
echo $conStr;
