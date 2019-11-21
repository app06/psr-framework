<?php

use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Middleware\ErrorHandler\ErrorHandlerMiddleware;
use Framework\Http\Middleware\ErrorHandler\ErrorResponseGenerator;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Router;
use Framework\Template\TemplateRenderer;
use Infrastructure\Framework\Http\Middleware\ErrorHandler\LogErrorListener;
use Infrastructure\Framework\Http\Middleware\ErrorHandler\PrettyErrorResponseGenerator;
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
                $middleware = new ErrorHandlerMiddleware(
                    $container->get(ErrorResponseGenerator::class)
                );

                $middleware->addListener($container->get(LogErrorListener::class));
                return $middleware;
            },
            ErrorResponseGenerator::class => function(ContainerInterface $container) {
                return new PrettyErrorResponseGenerator(
                    $container->get(TemplateRenderer::class),
                    new Response(),
                    [
                            '403' => 'error/403',
                            '404' => 'error/404',
                            'error' => 'error/error'
                    ]
                );
            },
            Psr\Log\LoggerInterface::class => function (ContainerInterface $container)
            {
                $logger = new Monolog\Logger('App');
                $logger->pushHandler(new Monolog\Handler\StreamHandler(
                    'var/log/application.log',
                    $container->get('config')['debug'] ? Monolog\Logger::DEBUG : Monolog\Logger::WARNING
                ));
                return $logger;
            },
        ],
    ],

    'debug' => false,
];