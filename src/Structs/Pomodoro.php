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
readonly class Pomodoro implements Struct
{
    /**
     * @param int|null $id
     * @param int $userId
     * @param string $startedAt
     * @param string|null $endedAt
     * @param int $breakDuration
     * @param string $timezone
     */
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $startedAt,
        public ?string $endedAt,
        public int $breakDuration,
        public string $timezone,
    ) {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($this->timezone);
        }
    }

    /**
     * @throws Exception
     */
    public static function fromRow(array $row): self
    {
        return new Pomodoro(
            $row['id'],
            $row['user_id'],
            $row['started_at'],
            $row['ended_at'],
            $row['break_duration'],
            $row['timezone'],
        );
    }

    /**
     * @return array
     */
    public function toCreateParams(): array
    {
        return [
            ':user_id' => $this->userId,
            ':started_at' => $this->startedAt,
            ':break_duration' => $this->breakDuration,
            ':timezone' => $this->timezone,
        ];
    }

    /**
     * @param array $a Request body data
     * @param Setting $s
     * @param array $u User data
     * @return self
     * @throws Exception
     */
    public static function fromRequest(array $a, array $u, Setting $s): self
    {
        $tz = new TimeZoneService();
        return new Pomodoro(
            $a['id'] ?? null,
            $u['id'],
            $tz->tzToUtc($a['started_at'], $a['timezone']) ?? '',
            $tz->tzToUtc($a['ended_at'] ?? null, $a['timezone']),
            $a['break_duration'] ?? $s->shortRestDuration,
            $a['timezone'] ?? $s->timezone,
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this;
    }
}
