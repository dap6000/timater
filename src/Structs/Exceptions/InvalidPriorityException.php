<?php

namespace App\Structs\Exceptions;

use App\Structs\Task;
use UnexpectedValueException;

class InvalidPriorityException extends UnexpectedValueException
{
    public static function make(string $priority): self {
        $msg = 'Invalid task priority value given. Found ' . $priority;
        $msg .= PHP_EOL . 'Priority must be one of: ' . join(', ', Task::PRIORITIES);
        return new InvalidPriorityException($msg);
    }
}