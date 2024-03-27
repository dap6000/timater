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
     * @param PDO $pdo
     * @return array
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
        PDO $pdo,
    ): array {
        parent::getData($userId, $body, $args, $pdo);
        return ['message' => 'Goodbye!'];
    }
}
