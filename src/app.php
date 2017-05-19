<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Predis\Silex\ClientsServiceProvider;
use Snc\RedisBundle\Session\Storage\Handler\RedisSessionHandler;
use Moust\Silex\Provider\CacheServiceProvider;

$app = new Application();

$app->register(new Performance\DomainServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new DoctrineServiceProvider);
$app->register(new DoctrineOrmServiceProvider);
$app->register(new ClientsServiceProvider(), [
    'predis.clients' => [
        'sessions' => [
            'host' => '127.0.0.1',
            'database' => 1,
        ],
        'cache' => [
            'host' => '127.0.0.1',
            'database' => 2,
        ],
    ],
]);
$app->register(new CacheServiceProvider(), array(
    'cache.options' => array(
        'driver' => 'redis',
        'redis' => $app['predis']['cache'],
    )
));

$app['session.storage.handler'] = new RedisSessionHandler($app['predis']['sessions']);

return $app;
