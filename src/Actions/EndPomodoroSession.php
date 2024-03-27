<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use Exception;
use PDO;

/**
 * NOT declared final because it gets extended by Quit
 */
class EndPomodoroSession extends BaseAction
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
        $settingsModel = new SettingsModel(pdo: $pdo);
        $settings = $settingsModel->getCurrent(userId: $userId);
        $model = new PomodoroModel($pdo);
        $pomodoro = $model->getCurrent(userId: $userId);
        if (!is_null($pomodoro)) {
            $model->endPomodoroSession(
                userId: $userId,
                time: $body['ended_at'],
                timezone: $body['timezone']
            );
        }

        return [
            'break' => [
                'duration' => $pomodoro?->breakDuration
                    ?? $settings->shortRestDuration
            ]
        ];
    }
}
