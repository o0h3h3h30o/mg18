<?php

namespace App\Libraries;

use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Shows detailed errors (like development) for /admin routes only.
 * Public routes still show generic "Whoops" error page.
 */
class AdminExceptionHandler extends ExceptionHandler
{
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode
    ): void {
        $uri = (string) $request->getUri()->getPath();

        if (str_starts_with($uri, '/admin')) {
            ini_set('display_errors', '1');
        }

        parent::handle($exception, $request, $response, $statusCode, $exitCode);
    }
}
