<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\TasksModel;
use App\Structs\Task;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class GetAvailableTasks extends BaseAction
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
        $body = (array)$request->getParsedBody();
        $userId = intval($body['user']['id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $tasksModel = new TasksModel(pdo: $pdo);
            $available = $tasksModel->getAvailable(userId: $userId);
            $pdo->commit();

            return [
                'tasks' => array_map(
                    fn(Task $t) => $t->toArray(),
                    $available
                )
            ];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
