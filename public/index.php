<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Middleware\MethodOverrideMiddleware;

use function Symfony\Component\String\s;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

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

$app->get('/', function ($request, $response) {
    // return $response->write('hello world');
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->run();
