<?php

namespace App\Models;

use App\Data\SQL;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Pomodoro;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;

class PomodoroModel extends Model
{

    public function getCurrent(): Pomodoro {
        try {
            $this->pdo->beginTransaction();
            $currentPomodoroSessionStmt = $this->pdo->prepare(query: SQL::GETCURRENTPOMODOROSESSION);
            $currentPomodoroSessionStmt->execute([]);
            $this->pdo->commit();

            return Pomodoro::fromRow($currentPomodoroSessionStmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    public function startPomodoroSession(Pomodoro $pomodoro): Pomodoro {
        try {
            $this->pdo->beginTransaction();
            $this->endPomodoroSession($pomodoro->startedAt, $pomodoro->timezone);
            $startPomodoroStmt = $this->pdo->prepare(query: SQL::CREATEPOMODOROSESSION);
            $startPomodoroStmt->execute(params: $pomodoro->toCreateParams());
            $tasksModel = new TasksModel();
            $task = $tasksModel->getActive();
            if (!is_nuLL($task)) {
                $tasksModel->resume($task->id, $pomodoro->startedAt, $pomodoro->timezone);
            }
            $this->pdo->commit();

            return $this->getCurrent();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }

    }
    public function endPomodoroSession(DateTimeImmutable $time, string $timezone): void {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();
            $tasksModel = new TasksModel();
            $task = $tasksModel->getActive();
            if (!is_nuLL($task)) {
                $tasksModel->pause($task->id, $utcTime, $timezone);
            }
            $endPomodoroStmt = $this->pdo->prepare(query: SQL::ENDPOMODORO);
            $endPomodoroStmt->execute(params: [':ended_at' => $utcTime->format('Y-m-d H:i:s')]);
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
}