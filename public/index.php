<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;
use Valitron\Validator;
use Hexlet\Code\Database\Connection;
use Hexlet\Code\Database\UrlsDB;
use Hexlet\Code\Database\ChecksDB;
use Carbon\Carbon;

use function Symfony\Component\String\s;

require_once __DIR__ . '/../vendor/autoload.php';
session_start();
try {
    $pdo = Connection::get()->connect();
} catch (\PDOException $e) {
    echo $e->getMessage();
}
$urlsPdo = new UrlsDB($pdo);
$checksPdo = new ChecksDB($pdo);

$container = new Container();
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
$router = $app->getRouteCollector()->getRouteParser();

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

$app->get('/urls/{id}', function ($request, $response, $args) use ($urlsPdo, $checksPdo) {
    $id = (int)$args['id'];
    $urlArray = $urlsPdo->selectUrl($id)[0];
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
// <?= "/urls/" . $url['id'] . "/checks"
$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($checksPdo, $urlsPdo, $router) {
    $urlId = $args['url_id'];
    $lastCheckTime = $checksPdo->insertCheck($urlId);
    $urlsPdo->insertLastCheck($urlId, $lastCheckTime);
    $link = $router->urlFor('url', ['id' => $urlId]);
    return $response->withRedirect($link, 302);
});

$app->run();
