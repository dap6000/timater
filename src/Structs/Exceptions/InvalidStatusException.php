<?php

declare(strict_types=1);

namespace App\Structs\Exceptions;

use App\Structs\Task;
use UnexpectedValueException;

/**
 *
 */
class InvalidStatusException extends UnexpectedValueException
{
    /**
     * @param string $status
     * @return self
     */
    public static function make(string $status): self
    {
        $msg = 'Invalid task status value given. Found ' . $status;
        $msg .= PHP_EOL . 'Status must be one of: '
            . join(', ', Task::STATUSES);
        return new InvalidStatusException($msg);
    }
}
