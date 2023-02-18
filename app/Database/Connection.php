<?php

namespace Hexlet\Code\Database;

use Carbon\Carbon;

class Connection
{
    private static $conn; // ?object $conn = null;
    public function connect(): object
    {
        // $file = realpath('app/Database/database.ini');
        $file = realpath(__DIR__ . '/database.ini');
        // if (!file_exists($file)) {
        if ($file === false) {
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
            // throw new \Exception("Error reading database configuration file");
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
        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    public static function get(): object
    {
        if (static::$conn === null) {
            static::$conn = new static();
        }

        return static::$conn;
    }

    protected function __construct()
    {
    }
}
