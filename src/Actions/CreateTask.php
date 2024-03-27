<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use App\Models\TasksModel;
use App\Structs\Task;
use Exception;
use PDO;

/**
 *
 */
final class CreateTask extends BaseAction
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
        $setting = (new SettingsModel(pdo: $pdo))->getCurrent(userId: $userId);
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
        return ['task' => $task];
    }
}
