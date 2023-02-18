<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;
use Valitron\Validator;
use Hexlet\Code\Database\Connection;
use Hexlet\Code\Database\UrlsDB;
use Hexlet\Code\Database\ChecksDB;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use DiDom\Document;

use function Symfony\Component\String\s;

require_once __DIR__ . '/../vendor/autoload.php';
session_start();
try {
    $pdo = Connection::get()->connect();
} catch (\PDOException $e) {
    echo $e->getMessage();
}
$urlsPdo = new UrlsDB($pdo);  // взаимодействие с конкретными таблицами
$checksPdo = new ChecksDB($pdo);

if (!$urlsPdo->tableExists()) { // создание таблиц, если нет
    $urlsPdo->createTables();
}

$urlsPdo->clearData(30); // set min timeout for clear tables

$container = new Container(); // хранит информацию о флеш сообщениях, шаблонах.
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser(); // именованый роутинг

$app->get('/', function ($request, $response) {
    $messages = $this->get('flash')->getMessages();
    $params = ['flash' => $messages];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('main');

$app->get('/urls', function ($request, $response) use ($urlsPdo) {
    $urls = $urlsPdo->selectUrls();
    $params = [
        'urls' => $urls,
    ];
    return $this->get('renderer')->render($response, 'urls/index.phtml', $params);
})->setName('urls');

$app->get('/urls/{id}', function ($request, $response, $args) use ($urlsPdo, $checksPdo, $router) {
    $id = (int)$args['id'];
    $array = $urlsPdo->selectUrl($id);
    if (empty($array)) {
        $this->get('flash')->addMessage('not find', 'Page not find');
        $link = $router->urlFor('main');
        return $response->withRedirect($link, 302);
    }
    $urlArray = $array[0];
    $checks = $checksPdo->selectAllCheck($id);
    $messages = $this->get('flash')->getMessages();
    $params = [
        'url' => $urlArray,
        'flash' => $messages,
        'checks' => $checks,
    ];
    return $this->get('renderer')->render($response, 'urls/show.phtml', $params);
})->setName('url');

$app->post('/urls', function ($request, $response) use ($router, $urlsPdo) {
    $url = $request->getParsedBodyParam('url');
    $v = new Validator($url);
    $v->rules([
        'required' => ['name'],
        'url' => ['name'],
        'lengthMax' => [['name', 255]],
    ]);
    if ($v->validate()) {
        $parsedUrl = parse_url($url['name']);
        $urlName = "{$parsedUrl["scheme"]}://{$parsedUrl["host"]}";
        $id = $urlsPdo->isDouble($urlName);
        if ($id) {
            $pageId = $id;
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $pageId = $urlsPdo->insertUrls($urlName);
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        }
        $link = $router->urlFor('url', ['id' => $pageId]);
        return $response->withRedirect($link, 302);
    }
    $errors = true;
    $params = ['errors' => $errors];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
});

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($checksPdo, $urlsPdo, $router) {
    $urlId = $args['url_id'];
    $client = new GuzzleHttp\Client();
    $urlName = $urlsPdo->selectUrl($urlId)[0]['name'];
    try {
        $res = $client->request('GET', $urlName);
        $checksPdo->insertCheck($urlId, $res);
    } catch (ClientException $e) {
        // echo Psr7\Message::toString($e->getRequest());
        // echo Psr7\Message::toString($e->getResponse());
    }
    $link = $router->urlFor('url', ['id' => $urlId]);
    return $response->withRedirect($link, 302);
});

$app->run();
