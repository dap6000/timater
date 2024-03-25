<?php

namespace App\Structs\Exceptions;

use App\Structs\Task;

class InvalidSizeException extends \UnexpectedValueException
{
    public static function make(string $size): self {
        $msg = 'Invalid task size value given. Found ' . $size;
        $msg .= PHP_EOL . 'Sizes must be one of: ' . join(', ', Task::SIZES);
        return new InvalidSizeException($msg);
    }
}