<?php

use App\Http\Action;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Framework\Http\Router\AuraRouterAdapter;
use App\Http\Middleware;
use Framework\Http\Pipeline\Pipeline;
use Framework\Http\Pipeline\MiddlewareResolver;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$params = [
    'users' => ['admin' => 'password']
];

$aura = new Aura\Router\RouterContainer();
$routes = $aura->getMap();

$routes->get('home', '/', Action\HelloAction::class);
$routes->get('about', '/about', Action\AboutAction::class);
$routes->get('blog', '/blog', Action\Blog\IndexAction::class);
$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);
$routes->get('cabinet', '/cabinet', [
    Middleware\ProfilerMiddleware::class,
    new Middleware\BasicAuthMiddleware($params['users']),
    Action\CabinetAction::class
]);

$router = new AuraRouterAdapter($aura);
$resolver = new MiddlewareResolver();

### Running
$request = ServerRequestFactory::fromGlobals();
try {
    $result = $router->match($request);
    foreach ($result->getAttributes() as $attribute => $value) {
        $request = $request->withAttribute($attribute, $value);
    }
    $handlers = $result->getHandler();
    $pipeline = new Pipeline();
    foreach (is_array($handlers) ? $handlers : [$handlers] as $handler) {
        $pipeline->pipe($resolver->resolve($handler));
    }
    $response = $pipeline($request, new Middleware\NotFoundHandler());
} catch (RequestNotMatchedException $e){
    $handler = new Middleware\NotFoundHandler();
    $response = $handler($request);
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Sergey');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);