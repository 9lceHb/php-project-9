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
        $sql = "SELECT * FROM urls ORDER BY id DESC;;";
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
}
