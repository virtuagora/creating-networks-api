<?php

$container = $app->getContainer();
$settings = $container->get('settings');

$app->add(new App\Middleware\AuthenticationMiddleware($container->get('session')));
$app->add(new App\Middleware\CORSMiddleware($settings['cors'], $container->get('errorHandler')));
// $app->add(new \Tuupola\Middleware\Cors(array_merge($settings['cors'], [
//     'logger' => $container->logger,
//     'error' => function ($request, $response, $arguments) { return $response; },
// ])));
