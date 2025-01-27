<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use App\Models\TasksModel;
use App\Structs\Task;
use Exception;

/**
 *
 */
final class SplitTask extends BaseAction
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
        $id = intval($args['id']);
        $tasksModel = new TasksModel(pdo: $this->pdo);
        $setting = (new SettingsModel(pdo: $this->pdo))->getCurrent(
            userId: $userId
        );
        $childTasks = array_map(
            fn(array $requestData) => Task::fromRequest(
                $requestData,
                $body['user'],
                $setting
            ),
            $body['children']
        );
        $tasks = $tasksModel->split(
            id: $id,
            userId: $userId,
            children: $childTasks
        );

        return ['parent' => array_shift($tasks), 'children' => $tasks];
    }
}
