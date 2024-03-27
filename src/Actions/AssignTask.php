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
final class AssignTask extends BaseAction
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
        $userId = (!is_null($body['user']))
            ? (!is_null($body['user']['id'])) ? intval($body['user']['id']) : 0
            : 0;
        try {
            $pdo->beginTransaction();
            $tasksModel = new TasksModel(pdo: $pdo);
            $me = $tasksModel->assign(
                id: $id,
                userId: $userId,
                time: $body['time'],
                timezone: $body['timezone']
            );
            $pdo->commit();

            return ['task' => $me];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
