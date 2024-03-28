<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use Exception;

/**
 *
 */
final class GetCurrentSettings extends BaseAction
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
        $settings = (new SettingsModel(pdo: $this->pdo))
            ->getCurrent(userId: $userId)
            ->toArray();

        return ['settings' => $settings];
    }
}
