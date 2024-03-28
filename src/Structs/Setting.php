<?php

declare(strict_types=1);

namespace App\Structs;

use App\Structs\Interfaces\Struct;
use Exception;

/**
 *
 */
final readonly class Setting implements Struct
{
    /**
     * @param int $userId
     * @param int $sessionDuration
     * @param int $shortRestDuration
     * @param int $longRestDuration
     * @param int $longRestThreshold
     * @param int $rockBreakingThreshold
     * @param bool $useTaskPriority
     * @param bool $useTaskSize
     * @param string $timezone
     */
    public function __construct(
        public int $userId,
        public int $sessionDuration,
        public int $shortRestDuration,
        public int $longRestDuration,
        public int $longRestThreshold,
        public int $rockBreakingThreshold,
        public bool $useTaskPriority,
        public bool $useTaskSize,
        public string $timezone,
    ) {
    }

    /**
     * @param array $row
     * @return self
     * @throws Exception
     */
    public static function fromRow(array $row): self
    {
        return new Setting(
            $row['user_id'],
            $row['session_duration'],
            $row['short_rest_duration'],
            $row['long_rest_duration'],
            $row['long_rest_threshold'],
            $row['rock_breaking_threshold'],
            (bool)$row['use_task_priority'],
            (bool)$row['use_task_size'],
            $row['timezone'],
        );
    }

    /**
     * @param array $a
     * @param array $u
     * @return self
     */
    public static function fromRequest(array $a, array $u): self
    {
        return new Setting(
            $u['id'],
            $a['session_duration'],
            $a['short_rest_duration'],
            $a['long_rest_duration'],
            $a['long_rest_threshold'],
            $a['rock_breaking_threshold'],
            $a['use_task_priority'],
            $a['use_task_size'],
            $a['timezone'],
        );
    }

    /**
     * @return array
     */
    public function toEditParams(): array
    {
        return [
            ':user_id' => $this->userId,
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            // No need to include user_id in API output
            'session_duration' => $this->sessionDuration,
            'short_rest_duration' => $this->shortRestDuration,
            'long_rest_duration' => $this->longRestDuration,
            'long_rest_threshold' => $this->longRestThreshold,
            'rock_breaking_threshold' => $this->rockBreakingThreshold,
            'use_task_priority' => $this->useTaskPriority,
            'use_task_size' => $this->useTaskSize,
            'timezone' => $this->timezone,
        ];
    }
}
