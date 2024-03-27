<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\PomodoroModel;
use App\Models\SettingsModel;
use App\Structs\Pomodoro;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class StartPomodoroSession extends BaseAction
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
            $pdo->commit();

            return ['pomodoro' => $pomodoro];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
