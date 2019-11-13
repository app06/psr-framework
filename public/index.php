<?php

use App\Http\Action;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Framework\Http\Router\AuraRouterAdapter;
use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$params = [
    'debug' => true,
    'users' => ['admin' => 'password']
];

$aura = new Aura\Router\RouterContainer();
$routes = $aura->getMap();

$routes->get('home', '/', Action\HelloAction::class);
$routes->get('about', '/about', Action\AboutAction::class);
$routes->get('blog', '/blog', Action\Blog\IndexAction::class);
$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);
$routes->get('cabinet', '/cabinet',Action\CabinetAction::class);

$router = new AuraRouterAdapter($aura);
$resolver = new MiddlewareResolver(new Response());
$app = new Application($resolver, new Middleware\NotFoundHandler());

$app->pipe(new Middleware\ErrorHandlerMiddleware($params['debug']));
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe('cabinet', new Middleware\BasicAuthMiddleware($params['users']));
$app->pipe(new Framework\Http\Middleware\RouteMiddleware($router));
$app->pipe(new Framework\Http\Middleware\DispatchMiddleware($resolver));

### Running
$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);