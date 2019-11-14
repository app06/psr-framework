<?php

use Framework\Http\Application;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * @var \Framework\Container\ContainerInterface $container
 * @var Application $app
 */

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$container = require 'config/container.php';
$app = $container->get(Application::class);

require 'config/pipeline.php';
require 'config/routes.php';

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

$emitter = new SapiEmitter();
$emitter->emit($response);