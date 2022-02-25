<?php
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    //Cors
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        return $response
                ->withHeader('Access-Control-Allow-Origin', $_ENV['API_IS_DEBUG'] == "true" ? '*':$_ENV['URL'])
                ->withHeader('Access-Control-Allow-Headers', 'X-Authorization, Content-Type, Accept, Origin')
                ->withHeader('Access-Control-Expose-Headers', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    $app->group(
        '/v1/{lang:(?:kk|ru)}',
        function (RouteCollectorProxy $app) {

            $app->group('/center', function (RouteCollectorProxy $app) {

                $app->group('/sign-in', function (RouteCollectorProxy $app) {

                    $app->post('/ecp', \App\Action\Sign\Center\SignInEcpAction::class);

                });

                $app->group('', function (RouteCollectorProxy $app) {

                    $app->group('/company', function (RouteCollectorProxy $app) {

                        $app->post('/add', \App\Action\Center\Company\CompanyAddAction::class);

                        $app->group('/{id:[0-9]+}', function (RouteCollectorProxy $app) {

                            $app->get('', \App\Action\Center\Company\CompanyReadAction::class);

                            $app->group('/admin', function (RouteCollectorProxy $app) {

                                $app->post('/add', \App\Action\Center\Company\Admin\AddAction::class);

                            });

                        });

                    });

                })->add(\App\Middleware\CenterAdminMiddleware::class);

            });

        });
};