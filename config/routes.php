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
                ->withHeader('Access-Control-Allow-Headers', 'X-Auth, Content-Type, Accept, Origin')
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

                        $app->post('', \App\Action\Center\Company\CompanyAddAction::class);

                        $app->get('', \App\Action\Center\Company\CompanyReadAction::class);

                        $app->group('/{bin:[0-9]+}', function (RouteCollectorProxy $app) {

                            $app->patch('', \App\Action\Center\Company\CompanyUpdateAction::class);

                            $app->get('', \App\Action\Center\Company\CompanyInfoReadAction::class);

                            $app->group('/admin', function (RouteCollectorProxy $app) {

                                $app->get('', \App\Action\Center\Company\Admin\ReadAction::class);

                                $app->post('', \App\Action\Center\Company\Admin\AddAction::class);

                                $app->patch('/{id:[0-9]+}', \App\Action\Center\Company\Admin\UpdateAction::class);

                            });

                        });

                    });

                    $app->group('/applicant', function (RouteCollectorProxy $app) {

                        $app->get('', \App\Action\Applicant\ApplicantReadAction::class);

                        $app->post('', \App\Action\Applicant\ApplicantAddAction::class);

                        $app->patch('/{iin:[0-9]+}', \App\Action\Applicant\ApplicantUpdateAction::class);

                    });

                })->add(\App\Middleware\CenterAdminMiddleware::class);

            });

            $app->group('/company', function (RouteCollectorProxy $app) {

                $app->group('/sign-in', function (RouteCollectorProxy $app) {

                    $app->post('/ecp', \App\Action\Sign\Company\SignInEcpAction::class);

                });

                $app->group('', function (RouteCollectorProxy $app) {

                    $app->group('/free-place', function (RouteCollectorProxy $app) {

                        $app->get('', \App\Action\Company\FreePlace\ReadAction::class);

                        $app->post('', \App\Action\Company\FreePlace\AddAction::class);

                        $app->group('/{id:[0-9]+}', function (RouteCollectorProxy $app) {

                            $app->patch('', \App\Action\Company\FreePlace\EditAction::class);

                            $app->delete('', \App\Action\Company\FreePlace\DeleteAction::class);

                        });

                    });
                })->add(\App\Middleware\CompanyAdminMiddleware::class);

            });

            $app->group('/reference', function (RouteCollectorProxy $app) {

                $app->get('/admin', \App\Action\HandBook\AdminReadAction::class);

                $app->get('/place-status', \App\Action\HandBook\PlaceStatusReadAction::class);

                $app->get('/position', \App\Action\HandBook\PositionReadAction::class);

            });

        });
};