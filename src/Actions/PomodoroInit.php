<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Models\TasksModel;
use Exception;

/**
 *
 */
final class PomodoroInit extends BaseAction
{
    /**
     * @param int $userId
     * @param array $body
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
    ): array {
        $pomodoro = (new PomodoroModel($this->pdo))
            ->getCurrent(userId: $userId);
        $settings = (new SettingsModel($this->pdo))
            ->getCurrent(userId: $userId);
        $tasksModel = new TasksModel($this->pdo);
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
