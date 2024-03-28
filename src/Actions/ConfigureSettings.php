<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SettingsModel;
use App\Structs\Setting;
use Exception;

/**
 *
 */
final class ConfigureSettings extends BaseAction
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
        $new = Setting::fromRequest($body['settings'], $body['user']);
        $new = (new SettingsModel(pdo: $this->pdo))
            ->edit(settings: $new, userId: $userId)
            ->toArray();
        return ['settings' => $new];
    }
}
