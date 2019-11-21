<?php

use App\Http\Middleware;
use App\Http\Middleware\ErrorHandler\DebugErrorResponseGenerator;
use App\Http\Middleware\ErrorHandler\ErrorHandlerMiddleware;
use App\Http\Middleware\ErrorHandler\ErrorResponseGenerator;
use App\Http\Middleware\ErrorHandler\PrettyErrorResponseGenerator;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Router;
use Framework\Template\TemplateRenderer;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;

return [
    'dependencies' => [
        'abstract_factories' => [
            \Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory::class
        ],
        'factories' => [
            Application::class => function (ContainerInterface $container) {
                return new Application(
                    $container->get(MiddlewareResolver::class),
                    $container->get(Router::class),
                    new Middleware\NotFoundHandler($container->get(TemplateRenderer::class))
                );
            },
            Router::class => function () {
                return new AuraRouterAdapter(new Aura\Router\RouterContainer());
            },
            MiddlewareResolver::class => function (ContainerInterface $container) {
                return new MiddlewareResolver(new Response(), $container);
            },
            ErrorHandlerMiddleware::class => function(ContainerInterface $container) {
                return new ErrorHandlerMiddleware(
                    $container->get(ErrorResponseGenerator::class)
                );
            },
            ErrorResponseGenerator::class => function(ContainerInterface $container) {
                if ($container->get('config')['debug']) {
                    return new DebugErrorResponseGenerator(
                        $container->get(TemplateRenderer::class),
                        new Response(),
                        'error/error-debug'
                    );
                }
                return new PrettyErrorResponseGenerator(
                    $container->get(TemplateRenderer::class),
                    new Response(),
                    [
                            '403' => 'error/403',
                            '404' => 'error/404',
                            'error' => 'error/error'
                    ]
                );
            }
        ],
    ],
];