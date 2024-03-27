<?php

declare(strict_types=1);

namespace App\Structs\Exceptions;

use App\Structs\Task;
use UnexpectedValueException;

/**
 *
 */
class InvalidPriorityException extends UnexpectedValueException
{
    /**
     * @param string $priority
     * @return self
     */
    public static function make(string $priority): self
    {
        $msg = 'Invalid task priority value given. Found ' . $priority;
        $msg .= PHP_EOL . 'Priority must be one of: '
            . join(', ', Task::PRIORITIES);
        return new InvalidPriorityException($msg);
    }
}
