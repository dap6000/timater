<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\SQL;
use App\Structs\Setting;
use PDO;
use Exception;

/**
 *
 */
class SettingsModel extends Model
{
    /**
     * @param int $userId
     * @return Setting
     * @throws Exception
     */
    public function getCurrent(int $userId): Setting
    {
        $currentSettingsStatement = $this->pdo->prepare(
            query: SQL::CURRENTSETTINGS
        );
        $currentSettingsStatement->execute(params: [':user_id' => $userId]);
        $row = $currentSettingsStatement->fetch(mode: PDO::FETCH_ASSOC);

        return ($row !== false)
            ? Setting::fromRow($row)
            : throw new Exception(
                message: 'Unable to locate configured settings'
            );
    }

    /**
     * @param Setting $settings
     * @param int $userId
     * @return Setting
     * @throws Exception
     */
    public function edit(Setting $settings, int $userId): Setting
    {
        $this->pdo->prepare(query: SQL::EDITSETTINGS)
            ->execute(
                params: $settings->toEditParams() + [':user_id' => $userId]
            );

        return $this->getCurrent($userId);
    }
}
