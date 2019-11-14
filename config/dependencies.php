<?php

use App\Http\Middleware;
use Framework\Container\Container;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Router;
use Zend\Diactoros\Response;

return [
    Application::class => function (Container $container) {
        return new Application(
            $container->get(MiddlewareResolver::class),
            $container->get(Router::class),
            new Middleware\NotFoundHandler()
        );
    },

    Middleware\BasicAuthMiddleware::class => function (Container $container) {
        return new Middleware\BasicAuthMiddleware($container->get('config')['users']);
    },

    Middleware\ErrorHandlerMiddleware::class => function (Container $container) {
        return new Middleware\ErrorHandlerMiddleware($container->get('config')['debug']);
    },

    MiddlewareResolver::class => function (Container $container) {
        return new MiddlewareResolver(new Response(), $container);
    },

    Router::class => function () {
        return new AuraRouterAdapter(new Aura\Router\RouterContainer());
    }
];