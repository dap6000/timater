<?php

declare(strict_types=1);

namespace App\Actions;

use Exception;
use PDO;

/**
 *
 */
final class Quit extends EndPomodoroSession
{
    /**
     * @param int $userId
     * @param array $body
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
    ): array {
        parent::getData($userId, $body, $args);
        return ['message' => 'Goodbye!'];
    }
}
