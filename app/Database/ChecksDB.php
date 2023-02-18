<?php

namespace Hexlet\Code\Database;

use Carbon\Carbon;
use DiDom\Document;

class ChecksDB
{
    private object $pdo;

    public function __construct(object $pdo)
    {
        $this->pdo = $pdo;
    }

    private function prepareData(object $res): array
    {
        $html = $res->getBody()->getContents();
        $statusCode = $res->getStatusCode();
        $document = new Document($html);
        $title = $document->has('title') ? $document->find('title')[0]->text() : null;
        $h1 = $document->has('h1') ? mb_substr($document->find('h1')[0]->text(), 0, 255) : null;
        $content = $document->has('meta[name=description]')
        ? optional($document->find('meta[name=description]')[0])->attr('content')
        : null;
        return [
            'statusCode' => $statusCode,
            'title' => $title,
            'h1' => $h1,
            'description' => $content,
        ];
    }

    public function insertCheck(int $urlId, object $res): object
    {
        $data = $this->prepareData($res);
        $createdAt = Carbon::now();
        $sql = "INSERT INTO url_checks (url_id, created_at, status_code, h1, title, description) 
                VALUES(:url_id, :created_at, :status_code, :h1, :title, :description);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':url_id', $urlId);
        $stmt->bindValue(':created_at', $createdAt);
        $stmt->bindValue(':status_code', $data['statusCode']);
        $stmt->bindValue(':h1', $data['h1']);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->execute();
        // $stmt->execute($array);
        return $createdAt;
    }

    public function selectAllCheck(int $urlId): array
    {
        $sql = "SELECT * FROM url_checks WHERE url_id = {$urlId} ORDER BY id DESC;";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        // $stmt->execute($array);
        return $result;
    }
}
