<?php

declare(strict_types=1);

namespace App\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class Quit extends EndPomodoroSession
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return string[]
     * @throws Exception
     */
    public function getData(
        Request $request,
        Response $response,
        array $args
    ): array {
        parent::getData($request, $response, $args);
        return ['message' => 'Goodbye!'];
    }
}
