<?php

namespace App\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Response;
use function http_build_query;

/**
 * A generic responder.
 */
final class Responder
{
    /**
     * @var PhpRenderer
     */
    private $phpRenderer;

    /**
     * @var RouteParserInterface
     */
    private $routeParser;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * The constructor.
     *
     * @param PhpRenderer $phpRenderer The template engine
     * @param RouteParserInterface $routeParser The route parser
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(PhpRenderer $phpRenderer, RouteParserInterface $routeParser, ResponseFactoryInterface $responseFactory) {
        $this->phpRenderer = $phpRenderer;
        $this->responseFactory = $responseFactory;
        $this->routeParser = $routeParser;
    }

    /**
     * Create a new response.
     *
     * @return ResponseInterface The response
     */
    public function createResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse()->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Output rendered template.
     *
     * @param ResponseInterface $response The response
     * @param string $template Template pathname relative to templates directory
     * @param array<mixed> $data Associative array of template variables
     *
     * @return ResponseInterface The response
     */
    public function withTemplate(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->phpRenderer->render($response, $template, $data);
    }

    /**
     * Creates a redirect for the given url / route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function withRedirect(
        ResponseInterface $response,
        string $destination,
        array $queryParams = []
    ): ResponseInterface {
        if ($queryParams) {
            $destination = sprintf('%s?%s', $destination, http_build_query($queryParams));
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    }

    /**
     * Creates a redirect for the given url / route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $routeName The redirect route name
     * @param array<mixed> $data Named argument replacement data
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function withRedirectFor(ResponseInterface $response, string $routeName, array $data = [], array $queryParams = []): ResponseInterface {
        return $this->withRedirect($response, $this->routeParser->urlFor($routeName, $data, $queryParams));
    }

    /**
     * Write JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param mixed $data The data
     * @param int $options Json encoding options
     *
     * @return ResponseInterface The response
     */
    public function withJson(ResponseInterface $response, $data = null, int $options = 0): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string)json_encode($data, $options));

        return $response;
    }

    /**
     * Write success JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param mixed $message The message
     * @param mixed $data The data
     *
     * @return ResponseInterface The response
     */
    public function success(ResponseInterface $response, $message = null, $data = null): ResponseInterface {
        $response = $response->withHeader('Content-Type', 'application/json')->withStatus(StatusCodeInterface::STATUS_CREATED);
        $response->getBody()->write((string)json_encode(['status' => 'success', 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE));
        return $response;
    }

    /**
     * Write error JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param string $message The error message
     * @param int $status_code The status code of client
     * @param mixed $detail The detail of error
     *
     * @return ResponseInterface The response
     */
    public function error(string $message = null, int $status_code = StatusCodeInterface::STATUS_BAD_REQUEST,  $detail = null): ResponseInterface {
        $response = new Response();
        $response = $response
                ->withHeader('Access-Control-Allow-Origin', $_ENV['API_IS_DEBUG'] == "true" ? '*':$_ENV['URL'])
                ->withHeader('Access-Control-Allow-Headers', 'X-Authorization, Content-Type, Accept, Origin')
                ->withHeader('Access-Control-Expose-Headers', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->getBody()->write((string)json_encode(['status' => 'error', 'code' => $status_code, 'message' => $message, 'detail'=> $detail], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status_code);
    }

}
