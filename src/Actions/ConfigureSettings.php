<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use App\Structs\Setting;
use Exception;
use PDO;

/**
 *
 */
final class ConfigureSettings extends BaseAction
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
        $new = Setting::fromRequest($body['settings'], $body['user']);
        $new = (new SettingsModel(pdo: $pdo))
            ->edit(settings: $new, userId: $userId)
            ->toArray();
        return ['settings' => $new];
    }
}
