<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Models\TasksModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class PomodoroInit extends BaseAction
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
            $pomodoro = (new PomodoroModel($pdo))->getCurrent(userId: $userId);
            $settings = (new SettingsModel($pdo))->getCurrent(userId: $userId);
            $tasksModel = new TasksModel($pdo);
            $activeTask = $tasksModel->getActive(userId: $userId);
            $availableTasks = $tasksModel->getAvailable(userId: $userId);
            $pdo->commit();

            return [
                'settings' => $settings,
                'pomodoro' => $pomodoro,
                'active_task' => $activeTask,
                'available_tasks' => $availableTasks,
            ];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
