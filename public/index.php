<?php

use App\Http\Action;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\RouteCollection;
use Framework\Http\Router\Router;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$routes = new RouteCollection();

$routes->get('home', '/', Action\HelloAction::class);
$routes->get('about', '/about', Action\AboutAction::class);
$routes->get('blog', '/blog', Action\Blog\IndexAction::class);
$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class, ['id' => '\d+']);

$router = new Router($routes);

### Running
$request = ServerRequestFactory::fromGlobals();
try {
    $result = $router->match($request);
    foreach ($result->getAttributes() as $attribute => $value) {
        $request = $request->withAttribute($attribute, $value);
    }
    /** @var callable $action */
    $handler = $result->getHandler();
    $action = is_string($handler) ? new $handler() : $handler;
    $response = $action($request);
} catch (RequestNotMatchedException $e){
    $response = new HtmlResponse('Undefined page', 404);
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Sergey');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);