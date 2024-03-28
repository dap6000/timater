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
     * @return array
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
    ): array {
        $tasksModel = new TasksModel(pdo: $this->pdo);
        $available = $tasksModel->getAvailable(userId: $userId);

        return [
            'tasks' => array_map(
                fn(Task $t) => $t->toArray(),
                $available
            )
        ];
    }
}
