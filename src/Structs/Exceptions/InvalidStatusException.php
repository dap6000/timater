<?php

namespace App\Structs\Exceptions;

use App\Structs\Task;

class InvalidStatusException extends \UnexpectedValueException
{
    public static function make(string $status): self {
        $msg = 'Invalid task status value given. Found ' . $status;
        $msg .= PHP_EOL . 'Status must be one of: ' . join(', ', Task::STATUSES);
        return new InvalidStatusException($msg);
    }
}