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

    public function insertCheck($urlId, $statusCode)
    {
        $createdAt = Carbon::now();
        $sql = 'INSERT INTO url_checks (url_id, created_at, status_code) VALUES(:url_id, :created_at, :status_code);';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':url_id', $urlId);
        $stmt->bindValue(':created_at', $createdAt);
        $stmt->bindValue(':status_code', $statusCode);
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
