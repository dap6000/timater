<?php

declare(strict_types=1);

namespace App\Structs\Exceptions;

use App\Structs\Task;
use UnexpectedValueException;

/**
 *
 */
class InvalidSizeException extends UnexpectedValueException
{
    /**
     * @param string $size
     * @return self
     */
    public static function make(string $size): self
    {
        $msg = 'Invalid task size value given. Found ' . $size;
        $msg .= PHP_EOL . 'Sizes must be one of: '
            . join(', ', Task::SIZES);
        return new InvalidSizeException($msg);
    }
}
