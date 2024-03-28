<?php

namespace App\Actions;

use App\Models\ReportsModel;
use App\Models\UsersModel;

class ReportPauses extends BaseAction
{

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
        return $reportsModel->pausedTasks(userId: $reportId);
    }
}
