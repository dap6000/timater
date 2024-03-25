<?php

namespace App\Structs;

use DateTimeImmutable;

readonly class Setting implements Struct
{
    const int ID = 0;
    public function __construct(
        public int $sessionDuration,
        public int $shortRestDuration,
        public int $longRestDuration,
        public int $longRestThreshold,
        public int $rockBreakingThreshold,
        public bool $useTaskPriority,
        public bool $useTaskSize,
        public string $timezone,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $modifiedAt,
    ) {}

    public static function fromRow(array $row): self {
        return new Setting(
            $row['session_duration'],
            $row['short_rest_duration'],
            $row['long_rest_duration'],
            $row['long_rest_threshold'],
            $row['rock_breaking_threshold'],
            $row['use_task_priority'],
            $row['use_task_size'],
            $row['timezone'],
            $row['created_at'],
            $row['modified_at'],
        );
    }

    public function toEditParams(): array {
        return [
            ':session_duration' => $this->sessionDuration,
            ':short_rest_duration' => $this->shortRestDuration,
            ':long_rest_duration' => $this->longRestDuration,
            ':long_rest_threshold' => $this->longRestThreshold,
            ':rock_breaking_threshold' => $this->rockBreakingThreshold,
            ':use_task_priority' => $this->useTaskPriority,
            ':use_task_size' => $this->useTaskSize,
            ':timezone' => $this->timezone,
        ];
    }
}