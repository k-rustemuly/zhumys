<?php

use App\Factory\LoggerFactory;
use App\Handler\DefaultErrorHandler;
use Cake\Database\Connection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Selective\BasePath\BasePathMiddleware;
use Selective\Validation\Encoder\JsonEncoder;
use Selective\Validation\Middleware\ValidationExceptionMiddleware;
use Selective\Validation\Transformer\ErrorDetailsResultTransformer;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\StreamFactory;
use Semhoun\Mailer\Mailer;
use Predis\Client;
use Predis\ClientInterface;
use App\Helper\Language;
use App\Helper\File;
use App\Helper\Authorization;
use Slim\Views\Twig;
use App\Helper\Pki;
use App\Helper\StatGov;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    Twig::class => function () {
        $twig = Twig::create(MAIN_DIR . '/templates',['cache' => false,'debug' => $_ENV['API_IS_DEBUG'], 'auto_reload' => $_ENV['API_IS_DEBUG']]);
        $twig->getEnvironment()->addGlobal('url', $_ENV['URL']);
        return $twig;
    },

    Mailer::class => function (ContainerInterface $container) {
        $view = $container->get(Twig::class);
        $mailer = new Mailer($view, [
            'host'      => $_ENV['SMTP_HOST'],  // SMTP Host
            'port'      => $_ENV['SMTP_PORT'],  // SMTP Port
            'username'  => $_ENV['SMTP_USERNAME'],  // SMTP Username
            'password'  => $_ENV['SMTP_PASSWORD'],  // SMTP Password
            'protocol'  => $_ENV['SMTP_PROTOCOL']   // SSL or TLS
        ]);

        // Set the details of the default sender
        $mailer->setDefaultFrom($_ENV['SMTP_USERNAME'], 'Электронная система EDUS');
        return $mailer;
    },

    Pki::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        return new Pki($settings["pki_domain"], $_ENV["API_IS_DEBUG"], $_ENV["API_IS_DEBUG"]);
    },

    StatGov::class => function (ContainerInterface $container) {
        $stat = $container->get('settings')["stat_gov"];
        return new StatGov($stat["domain"], $stat["languages"]);
    },

    Language::class => function () {
        return new Language();
    },

    Authorization::class => function () {
        return new Authorization();
    },

    File::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        return new File($settings["uploads_dir"], $settings["uploads_public_dir"]);
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    StreamFactoryInterface::class => function () {
        return new StreamFactory();
    },

    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },

    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get('settings')['logger']);
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);

        return new BasePathMiddleware($app);
    },

    Connection::class => function (ContainerInterface $container) {
        return new Connection($container->get('settings')['db']);
    },

    PDO::class => function (ContainerInterface $container) {
        $db = $container->get(Connection::class);
        $driver = $db->getDriver();
        $driver->connect();

        return $driver->getConnection();
    },

    ValidationExceptionMiddleware::class => function (ContainerInterface $container) {
        $factory = $container->get(ResponseFactoryInterface::class);

        return new ValidationExceptionMiddleware(
            $factory,
            new ErrorDetailsResultTransformer(),
            new JsonEncoder()
        );
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['error'];
        $app = $container->get(App::class);

        $logger = $container->get(LoggerFactory::class)
            ->addFileHandler('error.log')
            ->createLogger();

        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details'],
            $logger
        );

        $errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));

        return $errorMiddleware;
    },

    ClientInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['redis'];

        return new Client($settings['server'], $settings['options']);
    },

];
