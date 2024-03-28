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
class PausesReport implements Struct
{
    /**
     * @param int $taskId
     * @param string $description
     * @param string $priority
     * @param string $size
     * @param string $status
     * @param string $begunAt
     * @param string $completedAt
     * @param non-empty-string $timezone
     * @param int $totalPauses
     * @param int $totalSeconds
     * @param float $totalMinutes
     * @param float $totalHours
     */
    public function __construct(
        public int $taskId,
        public string $description,
        public string $priority,
        public string $size,
        public string $status,
        public string $begunAt,
        public string $completedAt,
        public string $timezone,
        public int $totalPauses,
        public int $totalSeconds,
        public float $totalMinutes,
        public float $totalHours,
    ) {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($this->timezone);
        }
    }

    /**
     * @param array $row
     * @return self
     */
    public static function fromRow(array $row): self
    {
        return new PausesReport(
            $row['task_id'],
            $row['description'],
            $row['priority'],
            $row['size'],
            $row['status'],
            $row['begun_at'],
            $row['completed_at'],
            $row['timezone'],
            $row['total_pauses'],
            $row['total_seconds'],
            $row['total_minutes'],
            $row['total_hours'],
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
            'description' => $this->description,
            'priority' => $this->priority,
            'size' => $this->size,
            'status' => $this->status,
            'begun_at' => $tz->utcToTz($this->begunAt, $this->timezone) ?? '',
            'completed_at' => $tz->utcToTz($this->completedAt, $this->timezone)
                ?? '',
            'timezone' => $this->timezone,
            'total_pauses' => $this->totalPauses,
            'total_seconds' => $this->totalSeconds,
            'total_minutes' => $this->totalMinutes,
            'total_hours' => $this->totalHours,
        ];
    }
}
