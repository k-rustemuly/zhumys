<?php

use Selective\BasePath\BasePathMiddleware;
use Selective\Validation\Middleware\ValidationExceptionMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    //$app->add(TwigMiddleware::createFromContainer($app));
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(BasePathMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
