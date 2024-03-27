<?php

declare(strict_types=1);

namespace App\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
abstract class BaseAction implements Interfaces\Invokable
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws Exception
     */
    public function __invoke(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $json = json_encode(
            $this->getData(
                $request,
                $response,
                $args
            )
        );
        if ($json === false) {
            throw new Exception(message: 'Failed to parse json response.');
        }
        $response->getBody()->write($json);

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return array
     */
    abstract public function getData(
        Request $request,
        Response $response,
        array $args
    ): array;
}
