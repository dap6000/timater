<?php

declare(strict_types=1);

use App\Data\AppConnectionBuilder;
use App\Data\TestConnectionBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;

return [
    // Application settings
    'settings' => fn() => require __DIR__ . '/settings.php',

    App::class => function (ContainerInterface $container) {
        $app = AppFactory::createFromContainer($container);

        // Register routes
        (require __DIR__ . '/routes.php')($app);

        // Register middleware
        (require __DIR__ . '/middleware.php')($app);

        return $app;
    },
    PDO::class => function (ContainerInterface $container) {
        return ($container->get('settings')['env'] === 'test')
            ? (new TestConnectionBuilder())->connect()
            : (new AppConnectionBuilder())->connect();
    },

    // HTTP factories
    ResponseFactoryInterface::class => function (
        ContainerInterface $container
    ) {
        return $container->get(Psr17Factory::class);
    },

    ServerRequestFactoryInterface::class => function (
        ContainerInterface $container
    ) {
        return $container->get(Psr17Factory::class);
    },

    StreamFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    UploadedFileFactoryInterface::class => function (
        ContainerInterface $container
    ) {
        return $container->get(Psr17Factory::class);
    },

    UriFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    // The Slim RouterParser
    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)
            ->getRouteCollector()
            ->getRouteParser();
    },

    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Logger('app');

        $filename = sprintf('%s/app.log', $settings['path']);
        $level = $settings['level'];
        $rotatingFileHandler = new RotatingFileHandler(
            $filename,
            0,
            $level,
            true,
            0777
        );
        $rotatingFileHandler->setFormatter(
            new LineFormatter(null, null, false, true)
        );
        $logger->pushHandler($rotatingFileHandler);

        return $logger;
    },
];