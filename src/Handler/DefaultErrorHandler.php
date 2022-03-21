<?php

namespace App\Handler;

use App\Factory\LoggerFactory;
use App\Responder\Responder;
use DomainException;
use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;  
use Throwable;
use App\Helper\Language;
use App\Exception\FieldException;

/**
 * Default Error Renderer.
 */
final class DefaultErrorHandler {
    /**
     * @var Responder
     */
    private $responder;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param LoggerFactory $loggerFactory The logger factory
     */
    public function __construct(Responder $responder, ResponseFactoryInterface $responseFactory, LoggerFactory $loggerFactory) {
        $this->responder = $responder;
        $this->responseFactory = $responseFactory;
        $this->logger = $loggerFactory->addFileHandler("error.log")->createLogger();
        $this->language = new Language();
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param Throwable $exception The exception
     * @param bool $displayErrorDetails Show error details
     * @param bool $logErrors Log errors
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors): ResponseInterface {
        $lang = explode("/", $request->getUri()->getPath())[2] ?? "ru";
        $this->language->locale($lang);

        // Log error
        if ($logErrors) {
            $this->logger->error(
                sprintf(
                    "Error: [%s] %s, Method: %s, Path: %s",
                    $exception->getCode(),
                    implode(" ",$this->getExceptionText($exception)),
                    $request->getMethod(),
                    $request->getUri()->getPath()
                )
            );
        }

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);
        

        // Error message
        $errorMessage = $displayErrorDetails ? $this->getErrorMessage($exception, $statusCode, $displayErrorDetails) : null;
        if ($exception instanceof FieldException) {
            $errors = $exception->getErrors();
            $response = $this->responder->error($this->language->get("error")[$errors[0]["message"]], $statusCode, $errorMessage);
        } 
        else {
            $response = $this->responder->error($this->language->get("error")[$exception->getMessage()], $statusCode, $errorMessage);
        }

        return $response;
    }

    /**
     * Get http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    private function getHttpStatusCode(Throwable $exception): int{
        // Detect status code
        $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;

        if ($exception instanceof HttpException || $exception instanceof HttpUnauthorizedException) {
            $statusCode = (int)$exception->getCode();
        }

        if ($exception instanceof DomainException || $exception instanceof InvalidArgumentException) {
            // Bad request
            $statusCode = StatusCodeInterface::STATUS_BAD_REQUEST;
        }

        $file = basename($exception->getFile());
        if ($file === "CallableResolver.php") {
            $statusCode = StatusCodeInterface::STATUS_NOT_FOUND;
        }

        return $statusCode;
    }

    /**
     * Get error message.
     *
     * @param Throwable $exception The error
     * @param int $statusCode The http status code
     * @param bool $displayErrorDetails Display details
     *
     * @return array The message
     */
    private function getErrorMessage(Throwable $exception, int $statusCode, bool $displayErrorDetails): array{
        $reasonPhrase = $this->responseFactory->createResponse()->withStatus($statusCode)->getReasonPhrase();
        $errorMessage[] = sprintf("%s %s", $statusCode, $reasonPhrase);
        $detail = array();
        if ($displayErrorDetails === true) {
            $detail = $this->getExceptionText($exception);
        }
        return array_merge($errorMessage, $detail);
    }

    /**
     * Get exception text.
     *
     * @param Throwable $exception Error
     * @param int $maxLength The max length of the error message
     *
     * @return array The full error message
     */
    private function getExceptionText(Throwable $exception, int $maxLength = 0): array{
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $message = $exception->getMessage();
        $trace = $exception->getTrace();
        $error[] = sprintf("[%s] %s in %s on line %s.", $code, $message, $file, $line);
        return array_merge($error, $trace);
    }
}
