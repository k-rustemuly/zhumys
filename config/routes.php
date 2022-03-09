<?php
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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

    $app->post('/github', function (Request $request, Response $response){
        echo shell_exec('git pull origin master');
        return $response;
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

                    $app->group('/free-place', function (RouteCollectorProxy $app) {

                        $app->get('', \App\Action\Center\FreePlace\FreePlaceReadAction::class);

                        $app->group('/{id:[0-9]+}', function (RouteCollectorProxy $app) {

                            $app->get('', \App\Action\Center\FreePlace\FreePlaceAboutAction::class);

                            $app->post('/accept', \App\Action\Center\FreePlace\FreePlaceAcceptAction::class);

                            $app->post('/reject', \App\Action\Center\FreePlace\FreePlaceRejectAction::class);

                            $app->post('/generate', \App\Action\Center\FreePlace\FreePlaceGenerateAction::class);

                            $app->post('/publish', \App\Action\Center\FreePlace\FreePlacePublishAction::class);
                            
                            $app->group('/ranging/{ranging_id:[0-9]+}', function (RouteCollectorProxy $app) {

                                $app->get('', \App\Action\Ranging\AboutAction::class);

                            });
                        });

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

                            $app->post('/send', \App\Action\Company\FreePlace\SendAction::class);

                            $app->get('', \App\Action\Company\FreePlace\AboutAction::class);

                            $app->group('/ranging/{ranging_id:[0-9]+}', function (RouteCollectorProxy $app) {

                                $app->get('', \App\Action\Ranging\AboutAction::class);

                                $app->post('/interview', \App\Action\Ranging\InterviewAction::class);

                                $app->post('/reject', \App\Action\Ranging\RejectAction::class);

                                $app->post('/accept', \App\Action\Ranging\AcceptAction::class);

                            });

                        });

                    });
                })->add(\App\Middleware\CompanyAdminMiddleware::class);

            });

            $app->group('/reference', function (RouteCollectorProxy $app) {

                $app->get('/admin', \App\Action\HandBook\AdminReadAction::class);

                $app->get('/place-status', \App\Action\HandBook\PlaceStatusReadAction::class);

                $app->get('/position', \App\Action\HandBook\PositionReadAction::class);

                $app->get('/privilege', \App\Action\HandBook\PrivelegeReadAction::class);

                $app->get('/applicant-status', \App\Action\HandBook\ApplicantStatusReadAction::class);
                
            });

        });
};