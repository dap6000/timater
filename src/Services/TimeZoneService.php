<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

/**
 *
 */
class TimeZoneService
{
    public const string FORMAT = 'Y-m-d H:i:s';

    /**
     * Outgoing DB data should be filtered through this method to make sure
     * the UTC datetime data we are storing goes out in API responses localized
     * to the specified timezone.
     *
     * @param string|null $time
     * @param non-empty-string $tz
     * @return string|null
     * @throws Exception
     */
    public function utcToTz(?string $time, string $tz): ?string
    {
        return (is_null($time))
            ? null
            : (new DateTimeImmutable($time, new DateTimeZone($tz)))
                ->format(TimeZoneService::FORMAT);
    }

    /**
     * Incoming API input should be filtered through this method to make sure
     * we are storing datetime info in UTC.
     *
     * @param string|null $time
     * @param non-empty-string $tz
     * @return string|null
     * @throws Exception
     */
    public function tzToUtc(?string $time, string $tz): ?string
    {
        return (is_null($time))
            ? null
            : (new DateTimeImmutable($time, new DateTimeZone($tz)))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(TimeZoneService::FORMAT);
    }
}
