<?php

namespace App\Models;

use App\Data\SQL;
use App\Structs\Exceptions\InvalidStatusException;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Task;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;

class TasksModel extends Model
{
    /**
     * @return Task[]
     */
    public function getAvailable(): array {
        try {
            $this->pdo->beginTransaction();
            $availableTasksStmt = $this->pdo->prepare(query: SQL::AVAILBLETASKS);
            $availableTasksStmt->execute(params: []);
            $all = $availableTasksStmt->fetchAll(PDO::FETCH_ASSOC);
            $this->pdo->commit();
            return array_map(
                fn($row): Task => Task::fromRow($row),
                $all
            );
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }

    }
    public function get(int $id): Task {
        try {
            $this->pdo->beginTransaction();
            $getTaskStmt = $this->pdo->prepare(query: SQL::GETTASK);
            $getTaskStmt->execute(params: [':id' => $id]);
            $this->pdo->commit();

            return Task::fromRow($getTaskStmt->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function create(Task $task): Task {
        try {
            $this->pdo->beginTransaction();
            $createTaskStmt = $this->pdo->prepare(query: SQL::CREATETASK);
            $createTaskStmt->execute($task->toCreateParams());
            $this->pdo->commit();

            return $this->get($this->pdo->lastInsertId());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    /**
     * @param Task[] $tasks
     * @return array
     */
    public function createBatch(array $tasks): array {
        $valueClauses = [];
        $whereClauses = [];
        $params = [];
        $row = 0;
        foreach ($tasks as $task) {
            $valueClauses[] = SQL::createTaskValuesRow($row);
            $whereClauses[] = SQL::getTaskWhereRow($row);
            $params = array_merge($params, $task->toCreateParams($row));
            $row++;
        }
        $createSql = SQL::CREATETASKBATCHPARTIAL . implode(', ', $valueClauses);
        try {
            $this->pdo->beginTransaction();
            $createTaskBatchStmt = $this->pdo->prepare($createSql);
            $createTaskBatchStmt->execute($params);
            $getSql = SQL::GETTASKBATCHPARTIAL . implode(' OR ', $whereClauses);
            $getTaskBatchStmt = $this->pdo->prepare($getSql);
            $getTaskBatchStmt->execute($params);
            $all = $getTaskBatchStmt->fetchAll(PDO::FETCH_ASSOC);
            $this->pdo->commit();

            return array_map(
                fn($row): Task => Task::fromRow($row),
                $all
            );
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }

    }
    public function edit(Task $task): Task {
        try {
            $this->pdo->beginTransaction();
            $editTaskStmt = $this->pdo->prepare(query: SQL::EDITTASK);
            $editTaskStmt->execute();
            $this->pdo->commit();

            return $this->get($this->pdo->lastInsertId());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function updateStatus(int $id, string $status): Task {
        if (!in_array($status, Task::STATUSES)) {
            throw InvalidStatusException::make($status);
        }
        try {
            $this->pdo->beginTransaction();
            $updateStatusStmt = $this->pdo->prepare(query: SQL::UPDATETASKSTATUS);
            $updateStatusStmt->execute(params: [':id' => $id, ':status' => $status]);
            $this->pdo->commit();

            return $this->get($this->pdo->lastInsertId());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function begin(int $id, DateTimeImmutable $time, string $timezone): Task
    {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();
            $beginTaskStmt = $this->pdo->prepare(query: SQL::BEGINTASK);
            $beginTaskStmt->execute(
                [
                    ':id' => $id,
                    ':begun_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':timezone' => $timezone
                ]
            );
            $this->pdo->commit();

            return $this->get($this->pdo->lastInsertId());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @param Task[] $children
     * @return Task[]
     */
    public function split(int $id, array $children): array
    {
        try {
            $this->pdo->beginTransaction();
            $this->updateStatus($id, 'Split');
            $newTasks = $this->createBatch($children);
            $values = [];
            $params = [];
            $row = 0;
            foreach ($newTasks as $task) {
                $values = SQL::createSplitValuesRow($row);
                $params = array_merge($params, [":parent_id$row" => $id, ":child_id$row" => $task->id]);
                $row++;
            }
            $createSplitsSql = SQL::CREATESPLITBATCHPARTIAL . implode(', ', $values);
            $createSplitStmt = $this->pdo->prepare($createSplitsSql);
            $createSplitStmt->execute($params);
            $parent = $this->get($id);
            $this->pdo->commit();

            return array_merge([$parent], $newTasks);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function assign(int $id, DateTimeImmutable $time, string $timezone): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();
            $activeTaskStmt = $this->pdo->prepare(query: SQL::UPSERTACTIVETASK);
            $activeTaskStmt->execute(params:
                [
                    ':active_task_id' => Task::ACTIVETASKID,
                    ':task_id0' => $id,
                    ':task_id1' => $id
                ]
            );
            $me = $this->get($id);
            if ($me->status === 'Paused') {
                $this->resume($id, $time, $timezone);
            }
            $me = $this->begin($id, $time, $timezone);
            $pomodoroModel = new PomodoroModel();
            $pomodoro = $pomodoroModel->getCurrent();
            $createPomodoroTaskStmt = $this->pdo->prepare(query: SQL::CREATEPOMODOROTASK);
            $createPomodoroTaskStmt->execute(
                [
                    ':pomodoro_session_id' => $pomodoro->id,
                    ':task_id' => $id,
                    ':assigned_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':timezone' => $timezone,
                ]
            );
            $this->pdo->commit();

            return $me;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function pause(int $id, DateTimeImmutable $time, string $timezone) {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();

            $createPauseStmt = $this->pdo->prepare(query: SQL::CREATEPAUSE);
            $createPauseStmt->execute(params:
                [
                    ':task_id' => $id,
                    ':paused_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':timezone' => $timezone,
                ]
            );

            $pomodoroModel = new PomodoroModel();
            $pomodoro = $pomodoroModel->getCurrent();
            $unassignTaskStmt = $this->pdo->prepare(query: SQL::UNASSIGNTASK);
            $unassignTaskStmt->execute(params:
                [
                    ':unassigned_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':pomodoro_session_id' => $pomodoro->id,
                    ':task_id' => $id,
                ]
            );
            $this->pdo->commit();

            return $this->updateStatus($id, 'Paused');
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function resume(int $id, DateTimeImmutable $time, string $timezone): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();
            $resumeTaskStmt = $this->pdo->prepare(query: SQL::RESUMETASK);
            $resumeTaskStmt->execute(params:
                [
                    ':resume_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':timezone' => $timezone,
                    ':task_id' => $id
                ]
            );
            $this->pdo->commit();

            return $this->get($id);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
    public function complete(int $id, DateTimeImmutable $time, string $timezone): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $utcTime = $time->setTimezone(new DateTimeZone('UTC'));
        try {
            $this->pdo->beginTransaction();
            $completeTaskStmt = $this->pdo->prepare(query: SQL::COMPLETETASK);
            $completeTaskStmt->execute(params:
                [
                    ':completed_at' => $utcTime->format('Y-m-d H:i:s'),
                    ':id' => $id,
                ]
            );
            $this->pdo->commit();

            return $this->get($id);
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    public function getActive(): ?Task
    {
        try {
            $this->pdo->beginTransaction();
            $getActiveTaskStmt = $this->pdo->prepare(query: SQL::GETACTIVETASK);
            $getActiveTaskStmt->execute(params:
                [
                    ':id' => Task::ACTIVETASKID,
                ]
            );
            $taskArray = $getActiveTaskStmt->fetch(PDO::FETCH_ASSOC);
            $this->pdo->commit();

            return ($taskArray) ? Task::fromRow($taskArray) : null;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
}