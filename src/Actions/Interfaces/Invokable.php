<?php

declare(strict_types=1);

namespace App\Actions\Interfaces;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
interface Invokable
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response;
}
