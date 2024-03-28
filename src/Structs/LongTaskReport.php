<?php

declare(strict_types=1);

namespace App\Structs;

use App\Services\TimeZoneService;
use App\Structs\Exceptions\InvalidTimeZoneException;
use App\Structs\Interfaces\Struct;
use DateTimeZone;
use Exception;

/**
 *
 */
final readonly class LongTaskReport implements Struct
{
    /**
     * @param int $taskId
     * @param int $sessionCount
     * @param string $description
     * @param string $priority
     * @param string $size
     * @param string $status
     * @param string $begunAt
     * @param string $completedAt
     * @param non-empty-string $timezone
     */
    public function __construct(
        public int $taskId,
        public int $sessionCount,
        public string $description,
        public string $priority,
        public string $size,
        public string $status,
        public string $begunAt,
        public string $completedAt,
        public string $timezone,
    ) {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($this->timezone);
        }
    }

    /**
     * @inheritDoc
     */
    public static function fromRow(array $row): self
    {
        return new LongTaskReport(
            $row['task_id'],
            $row['sessions'],
            $row['description'],
            $row['priority'],
            $row['size'],
            $row['status'],
            $row['begun_at'],
            $row['completed_at'],
            $row['timezone'],
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        $tz = new TimeZoneService();
        return [
            'task_id' => $this->taskId,
            'session_count' => $this->sessionCount,
            'description' => $this->description,
            'priority' => $this->priority,
            'size' => $this->size,
            'status' => $this->status,
            'begun_at' => $tz->utcToTz($this->begunAt, $this->timezone) ?? '',
            'completed_at' => $tz->utcToTz($this->completedAt, $this->timezone)
                ?? '',
            'timezone' => $this->timezone,
        ];
    }
}
