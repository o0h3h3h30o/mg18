<?php

namespace App\Libraries;

use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AdminExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    ): void {
        $uri = (string) $request->getUri()->getPath();

        // /admin routes: show detailed errors
        if (str_starts_with($uri, '/admin')) {
            ini_set('display_errors', '1');
        }

        // Delegate to CI4's default handler
        $config = config('Exceptions');
        $handler = new ExceptionHandler($config);
        $handler->handle($exception, $request, $response, $statusCode, $exitCode);
    }
}
