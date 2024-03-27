<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\SQL;
use App\Services\TimeZoneService;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Pomodoro;
use DateTimeZone;
use Exception;
use PDO;

/**
 *
 */
class PomodoroModel extends Model
{
    /**
     * @param int $userId
     * @return ?Pomodoro
     * @throws Exception
     */
    public function getCurrent(int $userId): ?Pomodoro
    {
        $currentPomodoroSessionStmt = $this->pdo->prepare(
            query: SQL::GETCURRENTPOMODOROSESSION
        );
        $currentPomodoroSessionStmt->execute(params: ['user_id' => $userId]);
        $row = $currentPomodoroSessionStmt->fetch(mode: PDO::FETCH_ASSOC);
        return ($row !== false) ? Pomodoro::fromRow($row) : null;
    }

    /**
     * @param Pomodoro $pomodoro
     * @param int $userId
     * @param int|null $taskId
     * @return Pomodoro
     * @throws Exception
     */
    public function startPomodoroSession(
        Pomodoro $pomodoro,
        int $userId,
        ?int $taskId = null
    ): Pomodoro {
        $this->endPomodoroSession(
            userId: $userId,
            time: $pomodoro->startedAt,
            timezone: $pomodoro->timezone
        );
        $startPomodoroStmt = $this->pdo->prepare(
            query: SQL::CREATEPOMODOROSESSION
        );
        $startPomodoroStmt->execute(params: $pomodoro->toCreateParams());
        $tasksModel = new TasksModel($this->pdo);
        $activeTask = $tasksModel->getActive($userId);
        if (
            !is_null($activeTask) &&
            (is_null($taskId) || $taskId === $activeTask->id)
        ) {
            $tasksModel->resume(
                id: $activeTask->id ?? 0,
                userId: $userId,
                time: $pomodoro->startedAt,
                timezone: $pomodoro->timezone
            );
        } elseif (!is_null($taskId) && $tasksModel->get($taskId, $userId)) {
            if (!is_null($activeTask)) {
                $tasksModel->pause(
                    id: $activeTask->id ?? 0,
                    userId: $userId,
                    time: $pomodoro->startedAt,
                    timezone: $pomodoro->timezone
                );
            }
            $tasksModel->assign(
                id: $taskId,
                userId: $userId,
                time: $pomodoro->startedAt,
                timezone: $pomodoro->timezone
            );
        }
        $pomodoro = $this->getCurrent($userId);
        return (!is_null($pomodoro))
            ? $pomodoro
            : throw new Exception(message: 'Failed to start pomodoro session!');
    }

    /**
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return void
     * @throws Exception
     */
    public function endPomodoroSession(
        int $userId,
        string $time,
        string $timezone
    ): void {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $tasksModel = new TasksModel(pdo: $this->pdo);
        $task = $tasksModel->getActive(userId: $userId);
        if (!is_null($task)) {
            $tasksModel->pause(
                id: $task->id ?? 0,
                userId: $userId,
                time: $time,
                timezone: $timezone
            );
        }
        $endPomodoroStmt = $this->pdo->prepare(query: SQL::ENDPOMODORO);
        $endPomodoroStmt->execute(params: [
            ':user_id' => $userId,
            ':ended_at' => $utcTime
        ]);
    }
}
