<?php

namespace App\Structs;

use App\Structs\Exceptions\InvalidTimeZoneException;
use DateTimeImmutable;
use DateTimeZone;

readonly class Pomodoro implements Struct
{
    public function __construct(
        public int $id,
        public DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $endedAt,
        public int $breakDuration,
        public string $timezone,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $modifiedAt = null,
    ) {
        if (!in_array($timezone, DateTimeZone::listIdentifiers())) {
            throw InvalidTimeZoneException::make($this->timezone);
        }
    }

    /**
     * @throws \Exception
     */
    public static function fromRow(array $row): self
    {
        return new Pomodoro(
            $row['id'],
            (!is_null($row['started_at']))
                ? new DateTimeImmutable($row['started_at'], new DateTimeZone($row['timezone']))
                : null,
            (!is_null($row['ended_at']))
                ? new DateTimeImmutable($row['ended_at'], new DateTimeZone($row['timezone']))
                : null,
            $row['break_duration'],
            $row['timezone'],
            (!is_null($row['created_at']))
                ? new DateTimeImmutable($row['created_at'], new DateTimeZone($row['timezone']))
                : null,
            (!is_null($row['modified_at']))
                ? new DateTimeImmutable($row['modified_at'], new DateTimeZone($row['timezone']))
                : null,
        );
    }

    public function toCreateParams(): array {
        return [
            ':started_at' => $this->startedAt->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s'),
            ':break_duration' => $this->breakDuration,
            ':timezone' => $this->timezone,
        ];
    }
}