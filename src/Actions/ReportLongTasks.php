<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ReportsModel;
use App\Models\SettingsModel;
use App\Models\UsersModel;
use Exception;

/**
 *
 */
class ReportLongTasks extends BaseAction
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getData(
        int $userId,
        array $body,
        array $args,
    ): array {
        $reportId = $userId;
        $usersModel = new UsersModel(pdo: $this->pdo);
        $user = $usersModel->get(id: $userId);
        if ($user->role === 'Admin') {
            // Admin users can run reports on other users
            // Pull report ID from $args
            $reportId = (!empty($args['id']))
                ? intval($args['id'])
                : $userId;
        }
        $settingsModel = new SettingsModel(pdo: $this->pdo);
        // Get configured settings for reported user
        $settings = $settingsModel->getCurrent(userId: $reportId);
        $reportsModel = new ReportsModel(pdo: $this->pdo);
        return $reportsModel->longTasks(
            userId: $reportId,
            threshold: $settings->rockBreakingThreshold
        );
    }
}
