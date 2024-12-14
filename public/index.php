<?php

use Alex\Blog\Domain\Services\UserService;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

session_start();

// Інші налаштування додатку
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
]);
$twig->addGlobal('session', $_SESSION);

// Завантаження маршрутизації
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

$app->run();