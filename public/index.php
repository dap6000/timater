<?php

use App\Models\DB;
use App\Models\TasksModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

CONST SETTING_ID = 0;

// Instantiate App
$app = AppFactory::create();

// Add parser for JSON data in request bodies.
$app->addBodyParsingMiddleware();
// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $pdo = DB::makeConnection();
    $name = $args['name'];
    $response->getBody()->write("<p>Hello, $name!</p>");
    
    return $response;
});


/**
 * @param \Faker\Generator $faker
 * @param DateTimeImmutable $task_complete
 * @param array $priorities
 * @param array $sizes
 * @param $tz
 * @return array
 * @throws Exception
 */
function makeTask(\Faker\Generator $faker, DateTimeImmutable $task_complete, array $priorities, array $sizes, $tz): array
{
    $jigger = $faker->randomDigitNotNull() * $faker->randomDigitNotNull();
    $task_start = $task_complete->add(new DateInterval('PT' . $jigger . 'S'));
    $priority = $faker->randomElement($priorities);
    $size = $faker->randomElement($sizes);
    $size_index = array_search($size, $sizes);
    $size_multiplier = ($size_index + 1) ** 2;
    $task_base_minutes = $faker->numberBetween(5, 10);
    $task_total_minutes = $task_base_minutes * $size_multiplier;
    $task_complete = $task_start->add(new DateInterval('PT' . $task_total_minutes . 'M'));
    $task_struct = [
        ':description' => $faker->sentence(),
        ':priority' => $priority,
        ':size' => $size,
        ':status' => 'Completed',
        ':begun_at' => $task_start->format('Y-m-d H:i:s'),
        ':completed_at' => $task_complete->format('Y-m-d H:i:s'),
        ':timezone' => $tz,
    ];
    return $task_struct;
}

/**
 * @param DateTimeImmutable $start
 * @param mixed $current_settings
 * @param int $ps_count
 * @param $tz
 * @return array
 * @throws Exception
 */
function makePomodoroSession(DateTimeImmutable $start, mixed $current_settings, int $ps_count, $tz): array
{
    $ps_end = $start->add(new DateInterval('PT' . $current_settings->session_duration . 'M'));
    return [
        ':started_at' => $start->format('Y-m-d H:i:s'),
        ':ended_at' => $ps_end->format('Y-m-d H:i:s'),
        ':break_duration' => ($ps_count % $current_settings->long_rest_threshold === 0)
            ? $current_settings->long_rest_duration
            : $current_settings->short_rest_duration,
        ':timezone' => $tz,
    ];
}

$app->get('/db/seed', function (Request $request, Response $response) {
    $seed_start = time();
    $pdo = DB::makeConnection();
    $settings_sql = 'SELECT * FROM settings WHERE id = ?';
    $stmt = $pdo->prepare($settings_sql);
    $current_settings = $stmt->execute([SETTING_ID]) ? $stmt->fetch(PDO::FETCH_OBJ) : null;
    $start = new DateTimeImmutable('2024-01-08 08:06:33');
    $now = new DateTimeImmutable('2024-03-22 16:23:48');
    $faker = Faker\Factory::create();
    $priorities = ['Cold', 'Warm', 'Hot', 'Urgent'];
    $sizes = ['Short', 'Tall', 'Grande', 'Venti', 'Big Gulp'];
    $create_ps_sql = 'INSERT INTO pomodoro_sessions (started_at, ended_at, break_duration, timezone)
        VALUES (:started_at, :ended_at, :break_duration, :timezone);';
    $create_task_sql = 'INSERT INTO tasks (
            description,
            priority,
            size,
            status,
            begun_at,
            completed_at,
            timezone
        ) VALUES (
            :description,
            :priority,
            :size,
            :status,
            :begun_at,
            :completed_at,
            :timezone
        )';
    $upsert_active_task_sql = 'INSERT INTO active_task (id, task_id) VALUES (0, :task_id0) ON DUPLICATE KEY UPDATE task_id = :task_id1;';
    $create_ps_stmt = $pdo->prepare($create_ps_sql);
    $create_task_stmt = $pdo->prepare($create_task_sql);
    $upsert_active_task_stmt = $pdo->prepare($upsert_active_task_sql);

    if ($current_settings) {
        // counter for number of pomodoro sessions so far today
        $ps_count = 1;
        // flags for creating new structs
        $create_new_ps = false;
        $create_new_task = false;
        // configured time zone
        $tz = $current_settings->timezone;

        $ps_struct = makePomodoroSession($start, $current_settings, $ps_count, $tz);;
        $create_ps_stmt->execute($ps_struct);

        // Generate a reasonable approximation of a task (thanks Faker)
        $task_struct = makeTask($faker, $start, $priorities, $sizes, $tz);

        // We have our first pomodoro session created and we've generated values
        // for our first task. Begin loop logic to sort out if the task can be
        // completed within a single session. Loop will continue until we have
        // filled the business days between Monday January 8th, 2024 and Friday
        // March 22nd, 2024. This takes 770 pomodoro sessions and a random number
        // of tasks. Running inside the Docker containers on my host machine this
        // process runs in around 1 second.
        while ($start < $now) {
            if ($create_new_ps) {
                $ps_struct = makePomodoroSession($start, $current_settings, $ps_count, $tz);
                // We can always safely insert the new pomodoro session record immediately
                $create_ps_stmt->execute($ps_struct);
                $create_new_ps = false;
            }
            if ($create_new_task) {
                // Create a few seconds of buffer between tasks
                $task_struct = makeTask($faker, new DateTimeImmutable($task_struct[':completed_at']), $priorities, $sizes, $tz);
                $create_new_task = false;
            }
            if ($task_struct[':completed_at'] < $ps_struct[':ended_at']) {
                $create_task_stmt->execute($task_struct);
                $create_new_task = true;
            }
            if ($task_struct[':completed_at'] > $ps_struct[':ended_at']) {
                //$task_struct[':session_count']++;
                // INSERT INTO pomodoro_tasks ...
                $rest_duration = $current_settings->short_rest_duration;
                if ($ps_count % $current_settings->long_rest_threshold === 0) {
                    $rest_duration = $current_settings->long_rest_duration;
                }
                $rest_interval = new DateInterval('PT' . $rest_duration . 'M');
                $start = (new DateTimeImmutable($ps_struct[':ended_at']))->add($rest_interval);
                // is it quittin' time?
                if (intval($start->format('H')) >= 16) {
                    $ps_count = 1;
                    // Advance by at least one day, skip the weekend
                    do {
                        $start = $start->add(new DateInterval('P1D'));
                        $d = $start->format('D');
                    } while (in_array($d, ['Sat', 'Sun']));
                    // Roll the clock back to 8 am.
                    $start = new DateTimeImmutable($start->format('Y-m-d 08:00:s'));
                }
                $task_struct[':completed_at'] = (new DateTimeImmutable($task_struct[':completed_at']))
                    ->add(date_diff(new DateTimeImmutable($ps_struct[':ended_at']), $start))
                    ->format('Y-m-d H:i:s');
                $create_new_ps = true;
                $ps_count++;
            }
        }
        $seed_end = time();
        $execution_time = $seed_end - $seed_start;
        $response->getBody()->write("Completed  in  $execution_time seconds!");
    } else {
        $response->getBody()->write("Settings are not configured!");
    }

    return $response;
});

// Create Task
$app->post('/tasks/add', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*
     * string description - Text displayed as the task
       string priority - One of ['Cold', 'Warm', 'Hot', 'Urgent']
       string size - One of ['Short', 'Tall', 'Grande', 'Venti', 'Big Gulp']
       string status - One of ['Waiting', 'In Progress', 'Completed', 'Split', 'Paused'], (defaults to Waiting if not in dev environment)
       string begunAt - A date + time string
       string completedAt - A date + time string (only processed in dev environment)
       string timezone - A PHP Timezone string
     */
});
// Edit Task
$app->put('/tasks/edit/{id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// Split Task
$app->post('/tasks/split/{id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// List Available Tasks
$app->get('/tasks/available', function (Request $request, Response $response, array $args) {
    // TODO Put a service to work here
    $tasksModel = new TasksModel();
    $availableTasks = $tasksModel->getAvailable();
    $response->getBody()->write(json_encode($availableTasks));
    return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
});
// Assign Task
$app->post('/tasks/assign/{task_id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// Pause Task
$app->post('/tasks/pause/{task_id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// Complete Task
$app->post('/tasks/complete/{task_id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// Start Session
$app->post('/pomodoro/start', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// End Session
$app->post('/pomodoro/end/{id}', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});
// Quit
$app->post('/pomodoro/quit', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    /*…*/
});

$app->get('/test', function (Request $request, Response $response, array $args) {
    $tasksModel = new TasksModel();
    $tasksModel->begin(7, new DateTimeImmutable(), 'Invalid String');
    /*…*/
});


$app->run();