<?php

declare(strict_types=1);

namespace App\Actions;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
abstract class BaseAction implements Interfaces\Invokable
{
    /**
     * @param PDO $pdo
     */
    public function __construct(protected PDO $pdo)
    {
    }

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
        $body = (array)$request->getParsedBody();
        $userId = (!is_null($body['user']))
            ? (!is_null($body['user']['id'])) ? intval($body['user']['id']) : 0
            : 0;
        try {
            $this->pdo->beginTransaction();
            $json = json_encode(
                [
                    'data' =>
                        $this->getData(
                            userId: $userId,
                            body: $body,
                            args: $args,
                        )
                ]
            );
            $this->pdo->commit();
        } catch (Exception $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
        if ($json === false) {
            throw new Exception(message: 'Failed to parse json response.');
        }
        $response->getBody()->write($json);

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    }

    /**
     * @param int $userId
     * @param array $body
     * @param array $args
     * @return array
     */
    abstract public function getData(
        int $userId,
        array $body,
        array $args,
    ): array;
}
