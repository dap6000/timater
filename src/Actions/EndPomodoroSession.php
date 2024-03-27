<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * NOT declared final because it gets extended by Quit
 */
class EndPomodoroSession extends BaseAction
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function getData(
        Request $request,
        Response $response,
        array $args
    ): array {
        // TODO DI
        $pdo = DB::makeConnection();
        $body = (array)$request->getParsedBody();
        $userId = intval($body['user']['id'] ?? 0);
        try {
            $pdo->beginTransaction();
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
            $pdo->commit();

            return [
                'break' => [
                    'duration' => $pomodoro?->breakDuration
                        ?? $settings->shortRestDuration
                ]
            ];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
