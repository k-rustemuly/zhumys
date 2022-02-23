<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use App\Helper\Role;
use Slim\Exception\HttpUnauthorizedException;

class CenterAdminMiddleware extends Authorization
{
	/**
	 * JWT Auth existence
	 *
	 * @param  ServerRequestInterface  $request PSR-7 request
	 * @param  RequestHandlerInterface $handler PSR-15 request handler
	 *
	 * @return Response
	 */
	public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): Response{
		$arr = parent::init($request);
		if(!Role::isCenterAdmin($arr)) throw new HttpUnauthorizedException($request, "Unauthorized user");
        return $handler->handle($request->withAttribute(self::class, $arr));
	}
}