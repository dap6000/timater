<?php

declare(strict_types=1);

namespace App\Structs\Exceptions;

use UnexpectedValueException;

/**
 *
 */
class InvalidTimeZoneException extends UnexpectedValueException
{
    /**
     * @param string $timezone
     * @return self
     */
    public static function make(string $timezone): self
    {
        $msg = 'Invalid timezone value given. Found ' . $timezone;
        $msg .= PHP_EOL . 'Valid timezones include "UTC", "America/Chicago",';
        $msg .= '"Asia/Hong_Kong", "Pacific/Auckland", or "Europe/Kyiv".';
        $msg .= PHP_EOL . 'There are far too many to list here. You can view';
        $msg .= 'them by region at ';
        $msg .= 'https://www.php.net/manual/en/timezones.php.';
        return new InvalidTimeZoneException($msg);
    }
}
