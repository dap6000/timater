<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TasksModel;
use App\Structs\Task;
use Exception;
use PDO;

/**
 *
 */
final class GetAvailableTasks extends BaseAction
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
        $tasksModel = new TasksModel(pdo: $pdo);
        $available = $tasksModel->getAvailable(userId: $userId);

        return [
            'tasks' => array_map(
                fn(Task $t) => $t->toArray(),
                $available
            )
        ];
    }
}
