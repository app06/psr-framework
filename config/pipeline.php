<?php

use App\Http\Middleware;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;

/** @var \Framework\Http\Application $app */

$app->pipe(Middleware\ErrorHandlerMiddleware::class);
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe('cabinet', Middleware\BasicAuthMiddleware::class);

$app->pipe(RouteMiddleware::class);

$app->pipe(DispatchMiddleware::class);