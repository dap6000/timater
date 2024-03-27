<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TasksModel;
use Exception;
use PDO;

/**
 *
 */
final class CompleteTask extends BaseAction
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
        $id = intval($args['id']);
        $tasksModel = new TasksModel(pdo: $pdo);
        $task = $tasksModel->complete(
            id: $id,
            userId: $userId,
            time: $body['time'],
            timezone: $body['timezone']
        );

        return ['task' => $task];
    }
}
