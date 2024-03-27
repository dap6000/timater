<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Models\TasksModel;
use Exception;
use PDO;

/**
 *
 */
final class PomodoroInit extends BaseAction
{
    /**
     * @param int $userId
     * @param array $body
     * @param array $args
     * @param PDO $pdo
     * @return array
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
        PDO $pdo,
    ): array {
        $pomodoro = (new PomodoroModel($pdo))->getCurrent(userId: $userId);
        $settings = (new SettingsModel($pdo))->getCurrent(userId: $userId);
        $tasksModel = new TasksModel($pdo);
        $activeTask = $tasksModel->getActive(userId: $userId);
        $availableTasks = $tasksModel->getAvailable(userId: $userId);

        return [
            'settings' => $settings,
            'pomodoro' => $pomodoro,
            'active_task' => $activeTask,
            'available_tasks' => $availableTasks,
        ];
    }
}
