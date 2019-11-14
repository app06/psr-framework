<?php

use App\Http\Middleware;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;

/** @var \Framework\Container\Container $container */
/** @var \Framework\Http\Application $app */

$app->pipe($container->get(Middleware\ErrorHandlerMiddleware::class));
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe('cabinet', $container->get(Middleware\BasicAuthMiddleware::class));

$app->pipe($container->get(RouteMiddleware::class));

$app->pipe($container->get(DispatchMiddleware::class));