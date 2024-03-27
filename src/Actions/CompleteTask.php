<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\TasksModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class CompleteTask extends BaseAction
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function getData(
        Request $request,
        Response $response,
        array $args
    ): array {
        // TODO DI
        $pdo = DB::makeConnection();
        $id = intval($args['id']);
        $body = (array)$request->getParsedBody();
        $userId = intval($body['user']['id'] ?? 0);
        if ($id !== $body['id']) {
            throw new Exception('ID mismatch!');
        }
        try {
            $pdo->beginTransaction();
            $tasksModel = new TasksModel(pdo: $pdo);
            $task = $tasksModel->complete(
                id: $id,
                userId: $userId,
                time: $body['time'],
                timezone: $body['timezone']
            );
            $pdo->commit();

            return ['task' => $task];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
