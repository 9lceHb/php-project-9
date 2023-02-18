<?php

namespace Hexlet\Code\Database;

use Carbon\Carbon;

class UrlsDB
{
    private object $pdo;

    public function __construct(object $pdo)
    {
        $this->pdo = $pdo;
    }

    public function tableExists(): bool
    {
        try {
            $result = $this->pdo->query("SELECT 1 FROM urls LIMIT 1"); // формальный запрос
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function createTables(): object
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
            created_at timestamp
            );';
        $this->pdo->exec($sql2);
        $this->pdo->exec($sql1);

        return $this;
    }

    public function insertUrls(string $name): int
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

    public function selectUrl(int $id): array
    {
        $sql = "SELECT *
            FROM urls
            WHERE id = $id;";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }

    public function selectUrls(): array
    {
        $sql = "WITH main AS (WITH temp_table AS (SELECT url_id, MAX(created_at) as max_created FROM url_checks
            GROUP BY url_id)
            SELECT url_checks.url_id AS id, url_checks.created_at as last_check, url_checks.status_code
            FROM url_checks JOIN temp_table
            ON url_checks.url_id = temp_table.url_id
            WHERE url_checks.created_at = temp_table.max_created)
            SELECT urls.id, urls.name, main.last_check, main.status_code FROM urls
            LEFT JOIN main ON urls.id = main.id
            ORDER BY id DESC;";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);//(\PDO::FETCH_UNIQUE); //\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }

    public function isDouble(string $url): mixed
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

    public function clearData(int $min)
    {
        $currentTime = Carbon::now();
        $sql = "SELECT MAX(GREATEST(urls.created_at, url_checks.created_at)) FROM urls
            LEFT JOIN url_checks
            ON urls.id = url_checks.url_id;";
        $stmt = $this->pdo->query($sql);
        $maxTimeStr = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['max'];
        if ($maxTimeStr !== null) {
            $maxTime = Carbon::createFromFormat('Y-m-d H:i:s', $maxTimeStr);
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
