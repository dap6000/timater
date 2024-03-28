<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Structs\Pomodoro;
use Exception;

/**
 *
 */
final class StartPomodoroSession extends BaseAction
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
        $settings = (new SettingsModel(pdo: $this->pdo))->getCurrent(
            userId: $userId
        );
        $newPomodoro = Pomodoro::fromRequest(
            $body['pomodoro'],
            $body['user'],
            $settings
        );
        $taskId = $body['task']->id ?? null;
        $model = new PomodoroModel(pdo: $this->pdo);
        $pomodoro = $model->startPomodoroSession(
            pomodoro: $newPomodoro,
            userId: $userId,
            taskId: $taskId
        );

        return ['pomodoro' => $pomodoro];
    }
}
