<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\TasksModel;
use App\Structs\Task;
use Exception;

/**
 *
 */
final class EditTask extends BaseAction
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
        $changes = $body['task'];
        if ($id !== intval($changes['id'])) {
            throw new Exception(message: 'ID mismatch!');
        }
        $tasksModel = new TasksModel(pdo: $this->pdo);
        $current = $tasksModel->get(id: $id, userId: $userId)->toArray();
        $current['description'] = $changes['description'];
        $current['priority'] = $changes['priority'];
        $current['size'] = $changes['size'];
        // toArray() removes the user_id key so let's put it back
        $current['user_id'] = $userId;
        $task = $tasksModel->edit(Task::fromRow($current))->toArray();

        return ['task' => $task];
    }
}
