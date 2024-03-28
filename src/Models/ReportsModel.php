<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\SQL;
use App\Structs\LongTaskReport;
use App\Structs\PausesReport;
use App\Structs\Task;
use Exception;
use PDO;

/**
 *
 */
class ReportsModel extends Model
{
    /**
     * @param int $userId
     * @param int $threshold
     * @return array|string[]
     */
    public function longTasks(int $userId, int $threshold): array
    {
        $reportStmt = $this->pdo->prepare(query: SQL::LONGTASKREPORT);
        $reportStmt->execute(params: [
            ':user_id' => $userId,
            ':threshold' => $threshold
        ]);
        $reportData = $reportStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($reportData))
            ? [
                'report' => array_map(
                    fn($row): LongTaskReport => LongTaskReport::fromRow($row),
                    $reportData
                )
            ]
            : ['report' => 'No tasks found with excessive session counts.'];
    }

    /**
     * @param int $userId
     * @return array|string[]
     */
    public function pausedTasks(int $userId): array
    {
        $reportStmt = $this->pdo->prepare(query: SQL::REPORTPAUSES);
        $reportStmt->execute(params: [':user_id' => $userId]);
        $reportData = $reportStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($reportData))
            ? [
                'report' => array_map(
                    fn($row): PausesReport => PausesReport::fromRow($row),
                    $reportData
                )
            ]
            : ['report' => 'No paused tasks were found.'];
    }

    /**
     * @param int $userId
     * @return array|string[]
     * @throws Exception
     */
    public function splitTasks(int $userId): array
    {
        $reportStmt = $this->pdo->prepare(query: SQL::SPLITSREPORT);
        $reportStmt->execute(params: [':user_id' => $userId]);
        $reportData = $reportStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($reportData))
            ? [
                'report' => array_map(
                    function ($row): array {
                        $parent = json_decode($row['old_task'], true);
                        $children = json_decode($row['new_tasks'], true);
                        $parentTask = Task::fromRow($parent);
                        $childTasks = array_map(
                            fn($t): Task => Task::fromRow($t),
                            $children
                        );

                        return [
                            'split' => [
                                'num_new_tasks' => $row['num_new_tasks'],
                                'parent' => $parentTask,
                                'children' => $childTasks,
                            ]
                        ];
                    },
                    $reportData
                )
            ]
            : ['report' => 'No split tasks were found.'];
    }

    /**
     * @param int $userId
     * @param string $start
     * @param string $end
     * @return array|false
     */
    public function metricsBySize(
        int $userId,
        string $start,
        string $end
    ): array|false {
        $reportStmt = $this->pdo->prepare(query: SQL::METRICSBYSIZE);
        $reportStmt->execute(
            params: [
                ':user_id' => $userId,
                ':report_start' => $start,
                ':report_end' => $end,
            ]
        );
        // No need to translate these results. We can send them
        // straight out as an array.
        return $reportStmt->fetchAll(mode: PDO::FETCH_ASSOC)
            ?: ['No tasks to report on.'];
    }

    /**
     * @param int $userId
     * @param string $start A date or datetime string
     * @param string $end
     * @return array
     */
    public function metricsByPriority(
        int $userId,
        string $start,
        string $end
    ): array {
        $reportStmt = $this->pdo->prepare(query: SQL::METRICSBYPRIORITY);
        $reportStmt->execute(
            params: [
                ':user_id' => $userId,
                ':report_start' => $start,
                ':report_end' => $end,
            ]
        );
        // No need to translate these results. We can send them
        // straight out as an array.
        return $reportStmt->fetchAll(mode: PDO::FETCH_ASSOC)
            ?: ['No tasks to report on.'];
    }
}
