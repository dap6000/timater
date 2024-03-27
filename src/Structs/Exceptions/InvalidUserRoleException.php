<?php

declare(strict_types=1);

namespace App\Structs\Exceptions;

use App\Structs\User;
use UnexpectedValueException;

/**
 *
 */
class InvalidUserRoleException extends UnexpectedValueException
{
    /**
     * @param string $role
     * @return self
     */
    public static function make(string $role): self
    {
        $msg = 'Invalid user role given. Found ' . $role;
        $msg .= PHP_EOL . 'Status must be one of: '
            . join(', ', User::ROLES);
        return new InvalidUserRoleException($msg);
    }
}
