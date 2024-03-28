<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\SQL;
use App\Services\TimeZoneService;
use App\Structs\Exceptions\InvalidStatusException;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Task;
use DateTimeZone;
use Exception;
use PDO;

/**
 *
 */
class TasksModel extends Model
{
    /**
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function getAvailable(int $userId): array
    {
        $availableTasksStmt = $this->pdo->prepare(query: SQL::AVAILBLETASKS);
        $availableTasksStmt->execute(params: [':user_id' => $userId]);
        $all = $availableTasksStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($all))
            ? array_map(
                fn($row): Task => Task::fromRow($row),
                $all
            )
            : throw new Exception(message: 'Unable to find available tasks.');
    }

    /**
     * @param int $id
     * @param int $userId
     * @return Task
     * @throws Exception
     */
    public function get(int $id, int $userId): Task
    {
        $getTaskStmt = $this->pdo->prepare(query: SQL::GETTASK);
        $getTaskStmt->execute(params: [':id' => $id, ':user_id' => $userId]);
        $row = $getTaskStmt->fetch(mode: PDO::FETCH_ASSOC);

        return ($row !== false)
            ? Task::fromRow($row)
            : throw new Exception(
                message: 'Unable to find a Task record with ID ' . $id
            );
    }

    /**
     * @param Task $task
     * @param int $userId
     * @return Task
     * @throws Exception
     */
    public function create(Task $task, int $userId): Task
    {
        $createTaskStmt = $this->pdo->prepare(query: SQL::CREATETASK);
        $createTaskStmt->execute(params: $task->toCreateParams());
        $id = intval($this->pdo->lastInsertId());

        return ($id > 0)
            ? $this->get(id: $id, userId: $userId)
            : throw new Exception('Failed to create Task.');
    }

    /**
     * @param array $tasks
     * @return array
     * @throws Exception
     */
    public function createBatch(array $tasks): array
    {
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
        $createSql = SQL::CREATETASKBATCHPARTIAL
            . implode(', ', $valueClauses);
        $createTaskBatchStmt = $this->pdo->prepare(query: $createSql);
        $createTaskBatchStmt->execute($params);
        $getSql = SQL::GETTASKBATCHPARTIAL
            . implode(' OR ', $whereClauses);
        $getTaskBatchStmt = $this->pdo->prepare(query: $getSql);
        $getTaskBatchStmt->execute(params: $params);
        $all = $getTaskBatchStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($all))
            ? array_map(
                fn($row): Task => Task::fromRow($row),
                $all
            )
            : throw new Exception('Failed to create multiple Tasks.');
    }

    /**
     * @param Task $task
     * @return Task
     * @throws Exception
     */
    public function edit(Task $task): Task
    {
        $editTaskStmt = $this->pdo->prepare(query: SQL::EDITTASK);
        $edits = $task->toEditParams();
        $editTaskStmt->execute(params: $edits);

        return $this->get(id: $edits[':id'], userId: $edits[':user_id']);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $status
     * @return Task
     * @throws Exception
     */
    public function updateStatus(int $id, int $userId, string $status): Task
    {
        if (!in_array($status, Task::STATUSES)) {
            throw InvalidStatusException::make($status);
        }
        $updateStatusStmt = $this->pdo->prepare(query: SQL::UPDATETASKSTATUS);
        $updateStatusStmt->execute(params: [
            ':id' => $id,
            ':user_id' => $userId,
            ':status' => $status
        ]);

        return $this->get(id: $id, userId: $userId);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return Task
     * @throws Exception
     */
    public function begin(
        int $id,
        int $userId,
        string $time,
        string $timezone
    ): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $beginTaskStmt = $this->pdo->prepare(query: SQL::BEGINTASK);
        $beginTaskStmt->execute(
            params: [
                ':id' => $id,
                ':user_id' => $userId,
                ':begun_at' => $utcTime,
                ':timezone' => $timezone
            ]
        );

        return $this->get(id: $id, userId: $userId);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param Task[] $children
     * @return array
     * @throws Exception
     */
    public function split(int $id, int $userId, array $children): array
    {
        $parent = $this->updateStatus(
            id: $id,
            userId: $userId,
            status: 'Split'
        );
        $newTasks = $this->createBatch(tasks: $children);
        $values = [];
        $params = [];
        $row = 0;
        foreach ($newTasks as $task) {
            $values[] = SQL::createSplitValuesRow($row);
            $params = array_merge($params, [
                ":user_id$row" => $userId,
                ":parent_id$row" => $id,
                ":child_id$row" => $task->id,
            ]);
            $row++;
        }
        $createSplitsSql = SQL::CREATESPLITBATCHPARTIAL
            . implode(', ', $values);
        $createSplitStmt = $this->pdo->prepare(query: $createSplitsSql);
        $createSplitStmt->execute(params: $params);

        return array_merge([$parent], $newTasks);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return Task
     * @throws Exception
     */
    public function assign(
        int $id,
        int $userId,
        string $time,
        string $timezone
    ): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $pomodoroModel = new PomodoroModel(pdo: $this->pdo);
        $pomodoro = $pomodoroModel->getCurrent(userId: $userId);
        if (is_null($pomodoro)) {
            throw new Exception(message: 'No active pomodoro session exists.');
        }
        $me = $this->get(id: $id, userId: $userId);
        if (
            !in_array(
                $me->status,
                ['Waiting', 'Paused']
            )
        ) {
            throw new Exception(
                message: 'Can only assigned a paused or waiting tasks.'
            );
        }
        $activeTaskStmt = $this->pdo->prepare(query: SQL::UPSERTACTIVETASK);
        $activeTaskStmt->execute(
            params: [
                ':user_id' => $userId,
                ':task_id0' => $id,
                ':task_id1' => $id
            ]
        );
        if ($me->status === 'Paused') {
            $this->resume(
                id: $id,
                userId: $userId,
                time: $time,
                timezone: $timezone
            );
        }

        $me = $this->begin(
            id: $id,
            userId: $userId,
            time: $time,
            timezone: $timezone
        );
        $createPomodoroTaskStmt = $this->pdo->prepare(
            query: SQL::CREATEPOMODOROTASK
        );

        $createPomodoroTaskStmt->execute(
            params: [
                ':user_id' => $userId,
                ':pomodoro_session_id' => $pomodoro->id,
                ':task_id' => $id,
                ':assigned_at' => $utcTime,
                ':timezone' => $timezone,
            ]
        );

        return $me;
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return Task
     * @throws Exception
     */
    public function pause(
        int $id,
        int $userId,
        string $time,
        string $timezone
    ): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $pomodoroModel = new PomodoroModel(pdo: $this->pdo);
        $pomodoro = $pomodoroModel->getCurrent(userId: $userId);
        if (is_null($pomodoro)) {
            throw new Exception(message: 'No active pomodoro session exists.');
        }
        $createPauseStmt = $this->pdo->prepare(query: SQL::CREATEPAUSE);
        $createPauseStmt->execute(
            params: [
                ':user_id' => $userId,
                ':task_id' => $id,
                ':paused_at' => $utcTime,
                ':timezone' => $timezone,
            ]
        );
        $unassignTaskStmt = $this->pdo->prepare(query: SQL::UNASSIGNTASK);
        $unassignTaskStmt->execute(
            params: [
                ':user_id' => $userId,
                ':unassigned_at' => $utcTime,
                ':pomodoro_session_id' => $pomodoro->id,
                ':task_id' => $id,
            ]
        );
        $this->deactivate(userId: $userId);

        return $this->updateStatus(id: $id, userId: $userId, status: 'Paused');
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return void
     * @throws Exception
     */
    public function resume(
        int $id,
        int $userId,
        string $time,
        string $timezone
    ): void {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $resumeTaskStmt = $this->pdo->prepare(query: SQL::RESUMETASK);
        $resumeTaskStmt->execute(params: [
            ':user_id' => $userId,
            ':resume_at' => $utcTime,
            ':timezone' => $timezone,
            ':task_id' => $id
        ]);
    }

    /**
     * @param int $id
     * @param int $userId
     * @param string $time
     * @param string $timezone
     * @return Task
     * @throws Exception
     */
    public function complete(
        int $id,
        int $userId,
        string $time,
        string $timezone
    ): Task {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($timezone);
        }
        $tz = new TimeZoneService();
        $utcTime = $tz->tzToUtc($time, $timezone);
        $active = $this->getActive(userId: $userId);
        if ($id !== $active?->id) {
            throw new Exception(message: 'Can only complete the active task!');
        }
        $completeTaskStmt = $this->pdo->prepare(query: SQL::COMPLETETASK);
        $completeTaskStmt->execute(
            params: [
                ':user_id' => $userId,
                ':completed_at' => $utcTime,
                ':id' => $id,
            ]
        );
        $this->deactivate(userId: $userId);

        return $this->get(id: $id, userId: $userId);
    }

    /**
     * @param int $userId
     * @return Task|null
     * @throws Exception
     */
    public function getActive(int $userId): ?Task
    {
        $getActiveTaskStmt = $this->pdo->prepare(query: SQL::GETACTIVETASK);
        $getActiveTaskStmt->execute(params: [':user_id' => $userId]);
        $taskArray = $getActiveTaskStmt->fetch(mode: PDO::FETCH_ASSOC);

        return (is_array($taskArray)) ? Task::fromRow($taskArray) : null;
    }

    /**
     * @param int $userId
     * @return void
     */
    private function deactivate(int $userId): void
    {
        $activeTaskStmt = $this->pdo->prepare(query: SQL::UPSERTACTIVETASK);
        $activeTaskStmt->execute(
            params: [
                ':user_id' => $userId,
                ':task_id0' => null,
                ':task_id1' => null
            ]
        );
    }
}
