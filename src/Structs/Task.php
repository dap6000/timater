<?php

namespace App\Structs;

use App\Structs\Exceptions\InvalidPriorityException;
use App\Structs\Exceptions\InvalidSizeException;
use App\Structs\Exceptions\InvalidStatusException;
use App\Structs\Exceptions\InvalidTimeZoneException;
use DateTimeImmutable;
use DateTimeZone;

readonly class Task implements Struct
{
    const array PRIORITIES = ['Cold', 'Warm', 'Hot', 'Urgent'];
    const array SIZES = ['Short', 'Tall', 'Grande', 'Venti', 'Big Gulp'];
    const array STATUSES = ['Waiting', 'In Progress', 'Completed', 'Split', 'Paused'];
    const int ACTIVETASKID = 0;
    public function __construct(
        public int $id,
        public string $description,
        public string $priority,
        public string $size,
        public string $status,
        public ?DateTimeImmutable $begunAt,
        public ?DateTimeImmutable $completedAt,
        public string $timezone,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $modifiedAt = null,
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
     * @throws \Exception
     */
    public static function fromRow(array $row): self
    {
        return new Task(
            $row['id'],
            $row['description'],
            $row['priority'],
            $row['size'],
            $row['status'],
            (!is_null($row['begun_at']))
                ? new DateTimeImmutable($row['begun_at'], new DateTimeZone($row['timezone']))
                : null,
            (!is_null($row['completed_at']))
                ? new DateTimeImmutable($row['completed_at'], new DateTimeZone($row['timezone']))
                : null,
            $row['timezone'],
            (!is_null($row['created_at']))
                ? new DateTimeImmutable($row['created_at'], new DateTimeZone($row['timezone']))
                : null,
            (!is_null($row['modified_at']))
                ? new DateTimeImmutable($row['modified_at'], new DateTimeZone($row['timezone']))
                : null,
        );
    }

    /**
     * Values to slot into the query found at SQL::CREATETASK
     *
     * @return array
     */
    public function toCreateParams(?int $n = null): array {
        return [
            ':description' . (is_null($n)) ? '' : $n => $this->description,
            ':priority' . (is_null($n)) ? '' : $n => $this->priority,
            ':size' . (is_null($n)) ? '' : $n => $this->size,
            ':status' . (is_null($n)) ? '' : $n => $this->status,
            ':timezone' . (is_null($n)) ? '' : $n => $this->timezone,
        ];
    }
}