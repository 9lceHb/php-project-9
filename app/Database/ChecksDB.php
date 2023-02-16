<?php

namespace Hexlet\Code\Database;

use Carbon\Carbon;

class ChecksDB
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function insertCheck($urlId)
    {
        $createdAt = Carbon::now();
        $sql = 'INSERT INTO url_checks (url_id, created_at) VALUES(:url_id, :created_at);';
        $stmt = $this->pdo->prepare($sql);
        // $array = [
        //     ':name' => $name,
        //     ':created_at' => $createdAt,
        // ];
        $stmt->bindValue(':url_id', $urlId);
        $stmt->bindValue(':created_at', $createdAt);
        $stmt->execute();
        // $stmt->execute($array);
        return $createdAt;
    }

    public function selectAllCheck($urlId)
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = {$urlId};";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }
}
