<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Structs\Pomodoro;
use Exception;
use PDO;

/**
 *
 */
final class StartPomodoroSession extends BaseAction
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
        $settings = (new SettingsModel(pdo: $pdo))->getCurrent(
            userId: $userId
        );
        $newPomodoro = Pomodoro::fromRequest(
            $body['pomodoro'],
            $body['user'],
            $settings
        );
        $taskId = $body['task']->id ?? null;
        $model = new PomodoroModel(pdo: $pdo);
        $pomodoro = $model->startPomodoroSession(
            pomodoro: $newPomodoro,
            userId: $userId,
            taskId: $taskId
        );

        return ['pomodoro' => $pomodoro];
    }
}
