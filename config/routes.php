<?php

declare(strict_types=1);

// Define app routes

use App\Actions\AssignTask;
use App\Actions\CompleteTask;
use App\Actions\ConfigureSettings;
use App\Actions\CreateTask;
use App\Actions\EditTask;
use App\Actions\EndPomodoroSession;
use App\Actions\GetAvailableTasks;
use App\Actions\GetCurrentSettings;
use App\Actions\PauseTask;
use App\Actions\PomodoroInit;
use App\Actions\Quit;
use App\Actions\ReportLongTasks;
use App\Actions\ReportMetricsByPriority;
use App\Actions\ReportMetricsBySize;
use App\Actions\ReportPauses;
use App\Actions\ReportSplits;
use App\Actions\SplitTask;
use App\Actions\StartPomodoroSession;
use App\Middleware\ApiKeyMiddleware;
use Monolog\Logger;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Psr\Log\LoggerInterface;
use Slim\App;

return function (App $app) {
    // Instantiating here for adding to specific routes.
    $apiKeyMiddleware = new ApiKeyMiddleware(
        $app->getContainer()?->get(PDO::class)
    );
    $app->get(
        pattern: '/settings/current',
        callable: GetCurrentSettings::class
    )->addMiddleware($apiKeyMiddleware);
    $app->put(
        pattern: '/settings/configure',
        callable: ConfigureSettings::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/tasks/add',
        callable: CreateTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->put(
        pattern: '/tasks/edit/{id}',
        callable: EditTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/tasks/split/{id}',
        callable: SplitTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/tasks/available',
        callable: GetAvailableTasks::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/tasks/assign/{id}',
        callable: AssignTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/tasks/pause/{id}',
        callable: PauseTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/tasks/complete/{id}',
        callable: CompleteTask::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/pomodoro/start',
        callable: StartPomodoroSession::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/pomodoro/end',
        callable: EndPomodoroSession::class
    )->addMiddleware($apiKeyMiddleware);
    $app->post(
        pattern: '/pomodoro/quit',
        callable: Quit::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/init',
        callable: PomodoroInit::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/reports/tasks/long[/{id}]',
        callable: ReportLongTasks::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/reports/tasks/pauses[/{id}]',
        callable: ReportPauses::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/reports/tasks/splits[/{id}]',
        callable: ReportSplits::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/reports/metrics/priority[/{id}]',
        callable: ReportMetricsByPriority::class
    )->addMiddleware($apiKeyMiddleware);
    $app->get(
        pattern: '/reports/metrics/size[/{id}]',
        callable: ReportMetricsBySize::class
    )->addMiddleware($apiKeyMiddleware);
};
