<?php

declare(strict_types=1);

namespace App\Structs;

use App\Services\TimeZoneService;
use App\Structs\Exceptions\InvalidPriorityException;
use App\Structs\Exceptions\InvalidSizeException;
use App\Structs\Exceptions\InvalidStatusException;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Interfaces\Struct;
use DateTimeZone;
use Exception;

/**
 *
 */
readonly class Task implements Struct
{
    public const array PRIORITIES = ['Cold', 'Warm', 'Hot', 'Urgent'];
    public const array SIZES = ['Short', 'Tall', 'Grande', 'Venti', 'Big Gulp'];
    public const array STATUSES = [
        'Waiting',
        'In Progress',
        'Completed',
        'Split',
        'Paused'
    ];

    /**
     * @param int|null $id
     * @param int $userId
     * @param string $description
     * @param string $priority
     * @param string $size
     * @param string $status
     * @param string|null $begunAt
     * @param string|null $completedAt
     * @param non-empty-string $timezone
     */
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $description,
        public string $priority,
        public string $size,
        public string $status,
        public ?string $begunAt,
        public ?string $completedAt,
        public string $timezone
    ) {
        if (!in_array($this->priority, Task::PRIORITIES)) {
            throw InvalidPriorityException::make($this->priority);
        }
        if (!in_array($this->size, Task::SIZES)) {
            throw InvalidSizeException::make($this->size);
        }
        if (!in_array($this->status, Task::STATUSES)) {
            throw InvalidStatusException::make($this->status);
        }
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($this->timezone);
        }
    }

    /**
     * @throws Exception
     */
    public static function fromRow(array $row): self
    {
        return new Task(
            $row['id'],
            $row['user_id'],
            $row['description'],
            $row['priority'],
            $row['size'],
            $row['status'],
            $row['begun_at'],
            $row['completed_at'],
            $row['timezone']
        );
    }

    /**
     * @throws Exception
     */
    public static function fromRequest(array $a, array $u, Setting $s): Task
    {
        return new Task(
            $a['id'] ?? null,
            $u['id'],
            $a['description'],
            $a['priority'],
            $a['size'],
            'Waiting',
            null,
            null,
            $a['timezone'] ?? $s->timezone,
        );
    }

    /**
     * Values to slot into the query found at SQL::CREATETASK
     *
     * @param int|null $n
     * @return array
     */
    public function toCreateParams(?int $n = null): array
    {
        return [
            ":user_id$n" => $this->userId,
            ":description$n" => $this->description,
            ":priority$n" => $this->priority,
            ":size$n" => $this->size,
            ":timezone$n" => $this->timezone,
        ];
    }

    /**
     * Values to slot into the query found at SQL::EDITTASK
     *
     * @return array
     */
    public function toEditParams(): array
    {
        return [
            ':id' => $this->id,
            ':user_id' => $this->userId,
            ':description' => $this->description,
            ':priority' => $this->priority,
            ':size' => $this->size,
        ];
    }

    /**
     * @throws Exception
     */
    public function toArray(): array
    {
        $tz = new TimeZoneService();
        return [
            'id' => $this->id ?? null,
            // no need to return user_id in API data
            'description' => $this->description,
            'priority' => $this->priority,
            'size' => $this->size,
            'status' => $this->status,
            'begun_at' => $tz->utcToTz($this->begunAt, $this->timezone),
            'completed_at' => $tz->utcToTz($this->completedAt, $this->timezone),
            'timezone' => $this->timezone,
        ];
    }
}
