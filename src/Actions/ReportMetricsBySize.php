<?php

namespace App\Actions;

use App\Actions\BaseAction;
use App\Models\ReportsModel;
use App\Models\UsersModel;
use App\Services\TimeZoneService;

class ReportMetricsBySize extends BaseAction
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
        $tz = new TimeZoneService();
        $start = $tz->tzToUtc($body['start'], $body['timezone']);
        $end = $tz->tzToUtc($body['end'], $body['timezone']);
        $reportsModel = new ReportsModel(pdo: $this->pdo);
        return $reportsModel->metricsBySize(
            userId: $reportId,
            start: $start,
            end: $end
        );
    }
}
