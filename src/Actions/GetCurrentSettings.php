<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use Exception;
use PDO;

/**
 *
 */
final class GetCurrentSettings extends BaseAction
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
        $settings = (new SettingsModel(pdo: $pdo))
            ->getCurrent(userId: $userId)
            ->toArray();

        return ['settings' => $settings];
    }
}
