<?php

namespace Hexlet\Code\Database;

use Carbon\Carbon;

class UrlsDB
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    private function createTables()
    {
        $sql1 = 'CREATE TABLE url_checks (
            id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            url_id bigint REFERENCES urls (id),
            status_code int,
            h1 text,
            title text,
            description text,
            created_at timestamp
            );';

        $sql2 = 'CREATE TABLE urls (
            id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            name varchar(255),
            created_at timestamp,
            last_check timestamp,
            status_code integer
            );';
        $this->pdo->exec($sql2);
        $this->pdo->exec($sql1);

        return $this;
    }

    public function insertUrls($name)
    {
        $createdAt = Carbon::now();
        $sql = 'INSERT INTO urls (name, created_at) VALUES(:name, :created_at);';
        $stmt = $this->pdo->prepare($sql);
        // $array = [
        //     ':name' => $name,
        //     ':created_at' => $createdAt,
        // ];
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':created_at', $createdAt);
        $stmt->execute();
        // $stmt->execute($array);
        return $this->pdo->lastInsertId('urls_id_seq');
    }

    public function selectUrl($id)
    {
        $sql = "SELECT * FROM urls WHERE id = {$id};";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }

    public function selectUrls()
    {
        $sql = "SELECT * FROM urls ORDER BY id DESC;";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);//(\PDO::FETCH_UNIQUE); //\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }

    public function isDouble($url)
    {
        $sql = 'SELECT * FROM urls WHERE name = :name;';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $url);
        $stmt->execute();
        $array = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($array)) {
            $id = $array[0]['id'];
            return $id;
        }
        return false;
    }

    public function insertLastCheck($id, $lastCheckTime, $statusCode)
    {
        $sql = 'UPDATE urls SET last_check = :lastCheckTime WHERE id = :id;';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':lastCheckTime', $lastCheckTime);
        $stmt->execute();
        $sql = 'UPDATE urls SET status_code = :status_code WHERE id = :id;';
        $stmt2 = $this->pdo->prepare($sql);
        $stmt2->bindValue(':id', $id);
        $stmt2->bindValue(':status_code', $statusCode);
        $stmt2->execute();
        // $stmt->execute($array);
        return $id;
    }

    public function clearData($min)
    {
        $currentTime = Carbon::now();
        $sql = "SELECT MAX(GREATEST(created_at, last_check)) FROM urls;";
        $stmt = $this->pdo->query($sql);
        $maxTimeStr = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['max'];
        if ($maxTimeStr !== null) {
            $maxTime = Carbon::createFromFormat('Y-m-d H:i:s', $maxTimeStr) ?? null;
            $diff = $maxTime->diffInMinutes($currentTime);
            if ($min < $diff) {
                $sql1 = 'DROP TABLE urls;';
                $sql2 = 'DROP TABLE url_checks;';
                $stmt3 = $this->pdo->prepare($sql2);
                $stmt3->execute();
                $stmt2 = $this->pdo->prepare($sql1);
                $stmt2->execute();
                $this->createTables();
            }
        }
    }
}
