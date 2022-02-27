<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use \Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Slim\Exception\HttpUnauthorizedException;

class Authorization
{
	/**
	 * JWT Auth existence
	 *
	 * @param  ServerRequestInterface  $request PSR-7 request
	 *
	 * @return array 
	 */
	public function init(ServerRequestInterface $request): array
	{
		JWT::$leeway = intval($_ENV['JWT_LIVE_SEC']); 
		$header = 'X-Auth';
		$contentTypeHeaders = $request->getHeader($header);
		if (count($contentTypeHeaders) != 1 ) throw new HttpUnauthorizedException($request, "Unauthorized user");
		$jwt = $contentTypeHeaders[0];
        try{
            return (array) JWT::decode($jwt, $_ENV['JWT_KEY'], array('HS256'));
		}catch(ExpiredException $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}catch(SignatureInvalidException $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}catch(\DomainException $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}catch(\InvalidArgumentException $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}catch(\UnexpectedValueException $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}catch(\DateTime $e){
			throw new HttpUnauthorizedException($request, "Unauthorized user");
		}
        return array();
	}
}