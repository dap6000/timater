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
final class EditTask extends BaseAction
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
        $changes = $body['task'];
        if ($id !== intval($changes['id'])) {
            throw new Exception(message: 'ID mismatch!');
        }
        try {
            $pdo->beginTransaction();
            $tasksModel = new TasksModel(pdo: $pdo);
            $current = $tasksModel->get(id: $id, userId: $userId)->toArray();
            $current['description'] = $changes['description'];
            $current['priority'] = $changes['priority'];
            $current['size'] = $changes['size'];
            // toArray() removes the user_id key so let's put it back
            $current['user_id'] = $userId;
            $task = $tasksModel->edit(Task::fromRow($current))->toArray();
            $pdo->commit();

            return ['task' => $task];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
