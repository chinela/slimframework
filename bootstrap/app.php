<?php

use Respect\Validation\Validator as v;
session_start();

require '../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,

        'db' => [
            'driver' => 'mysql',
            'host' => 'db_host',
            'username' => 'db_username',
            'password' => 'db_password',
            'database' => 'db_name',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ]
    ]
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};



$container['auth'] = function($container) {
    return new App\Auth\Auth;
};

$container['flash'] = function($container){
    return new \Slim\Flash\Messages;
};


$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views/', [
        'cache' => false
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->geturi()

    ));

    $view->getEnvironment()->addGlobal('auth', [
        'user' => $container->auth->user(),
        'check' => $container->auth->check(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;

};


$container['HomeController'] = function($container) {
    return new App\Controllers\HomeController($container);
};

$container['validator'] = function($container) {
    return new App\validation\Validator;
};

$container['AuthController'] = function($container) {
    return new App\Controllers\AuthController($container);
};


$container['csrf'] = function($container) {
    return new \Slim\Csrf\Guard;
};

$app->add(new App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new App\Middleware\OldInputMiddleware($container));
$app->add(new App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);


v::with('App\\Validation\\Rules\\');


require_once '../router/routes.php';