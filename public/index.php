<?php

use App\Http\Action;
use Framework\Container\Container;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Router\Router;
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

$container = new Container();

$container->set('config', [
    'debug' => true,
    'users' => ['admin' => 'password']
]);

$container->set(Middleware\BasicAuthMiddleware::class, function (Container $container) {
    return new Middleware\BasicAuthMiddleware($container->get('config')['users']);
});

$container->set(Middleware\ErrorHandlerMiddleware::class, function (Container $container) {
    return new Middleware\ErrorHandlerMiddleware($container->get('config')['debug']);
});

$container->set(MiddlewareResolver::class, function () {
    return new MiddlewareResolver(new Response());
});

$container->set(Application::class, function (Container $container) {
    return new Application(
        $container->get(MiddlewareResolver::class),
        $container->get(Router::class),
        new Middleware\NotFoundHandler()
    );
});

$container->set(RouteMiddleware::class, function (Container $container) {
    return new RouteMiddleware($container->get(Router::class));
});

$container->set(DispatchMiddleware::class, function (Container $container) {
    return new DispatchMiddleware($container->get(MiddlewareResolver::class));
});

$container->set(Router::class, function () {
    return new AuraRouterAdapter(new Aura\Router\RouterContainer());
});

/* @var Application $app */
$app = $container->get(Application::class);

$app->pipe($container->get(Middleware\ErrorHandlerMiddleware::class));
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe('cabinet', $container->get(Middleware\BasicAuthMiddleware::class));
$app->pipe($container->get(RouteMiddleware::class));
$app->pipe($container->get(DispatchMiddleware::class));

$app->get('home', '/', Action\HelloAction::class);
$app->get('about', '/about', Action\AboutAction::class);
$app->get('blog', '/blog', Action\Blog\IndexAction::class);
$app->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class, ['tokens' => ['id' => '\d+']]);
$app->get('cabinet', '/cabinet', Action\CabinetAction::class);


### Running
$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);