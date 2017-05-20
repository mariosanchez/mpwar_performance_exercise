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
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

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


$app['s3Filesystem'] = function ($c) {
    $client = new S3Client([
        'credentials' => [
            'key'    => 'AKIAIDMQL5OQFJ6G7BLQ',
            'secret' => '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',
        ],
        'region' => 'eu-west-2',
        'version' => 'latest',
    ]);

    $adapter = new AwsS3Adapter($client, 'execice');

    return new Filesystem($adapter);
};

return $app;
