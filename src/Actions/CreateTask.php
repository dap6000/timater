<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\SettingsModel;
use App\Models\TasksModel;
use App\Structs\Task;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class CreateTask extends BaseAction
{
    /**
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
            $setting = (new SettingsModel($pdo))->getCurrent(userId: $userId);
            $newTask = Task::fromRequest(
                $body['task'],
                $body['user'],
                $setting
            );
            $tasksModel = new TasksModel(pdo: $pdo);
            $task = $tasksModel->create(
                task: $newTask,
                userId: $userId
            )->toArray();
            $pdo->commit();
            return ['task' => $task];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
