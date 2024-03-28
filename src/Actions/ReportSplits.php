<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ReportsModel;
use App\Models\UsersModel;
use Exception;

/**
 *
 */
class ReportSplits extends BaseAction
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
        $reportsModel = new ReportsModel(pdo: $this->pdo);
        return $reportsModel->splitTasks(userId: $reportId);
    }
}
