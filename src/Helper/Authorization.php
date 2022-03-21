<?php 
declare(strict_types=1);

namespace App\Helper;
use Psr\Http\Message\ServerRequestInterface;
use \Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use UnexpectedValueException;
use Slim\Exception\HttpUnauthorizedException;

class Authorization {
    
    /**
     *
     * @var array<mixed> $payload 
     */
    public $payload; 

    /**
     * Открываем токен jwt 
     *
     * @param ServerRequestInterface       $request
     * @throws HttpUnauthorizedException
     *
     */
    public function init(ServerRequestInterface $request) {
		$contentTypeHeaders = $request->getHeader("X-Authorization");
        if(isset($contentTypeHeaders[0])) {
            $jwt = $contentTypeHeaders[0];
            try {
                JWT::$leeway = intval($_ENV["JWT_LIVE_SEC"]); 
                $decoded = JWT::decode($jwt, $_ENV["JWT_KEY"], array("HS256"));
                $this->payload = (array) $decoded;
                return $this->payload;
            } catch(ExpiredException $e) {
                throw new HttpUnauthorizedException($request, "Unauthorized");
            } catch(SignatureInvalidException $e) {
                throw new HttpUnauthorizedException($request, "Unauthorized");
            } catch(UnexpectedValueException $e) {
                throw new HttpUnauthorizedException($request, "Unauthorized");
            } catch(InvalidArgumentException $e) {
                throw new HttpUnauthorizedException($request, "Unauthorized");
            }
        } else {
            throw new HttpUnauthorizedException($request, "Authorization key is not defined");
        }
    }
}