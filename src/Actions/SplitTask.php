<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\DB;
use App\Models\SettingsModel;
use App\Models\TasksModel;
use App\Structs\Task;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 *
 */
final class SplitTask extends BaseAction
{
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function getData(
        Request $request,
        Response $response,
        array $args
    ): array {
        // TODO DI
        $pdo = DB::makeConnection();
        $id = intval($args['id']);
        $body = (array)$request->getParsedBody();
        $userId = intval($body['user']['id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $tasksModel = new TasksModel(pdo: $pdo);
            $setting = (new SettingsModel(pdo: $pdo))->getCurrent(
                userId: $userId
            );
            $childTasks = array_map(
                fn(array $requestData) => Task::fromRequest(
                    $requestData,
                    $body['user'],
                    $setting
                ),
                $body['children']
            );
            $tasks = $tasksModel->split(
                id: $id,
                userId: $userId,
                children: $childTasks
            );
            $pdo->commit();

            return ['parent' => array_shift($tasks), 'children' => $tasks];
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }
}
