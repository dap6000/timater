<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/db.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('<a href="/hello/world">Try /hello/world</a>');
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $pdo = db();
    $name = $args['name'];
    $response->getBody()->write("<p>Hello, $name!</p>");
    if ($pdo) {
        $upsert_sql = 'INSERT INTO greetings (who, num) VALUES (?, 1) ON DUPLICATE KEY UPDATE num = num + 1;';
        $stmt = $pdo->prepare($upsert_sql);
        $stmt->execute([$name]);
        $select_sql = 'SELECT num FROM greetings WHERE who = ?;';
        $stmt = $pdo->prepare($select_sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        if ($result->num > 1) {
            $response->getBody()->write("<p>It's nice to see you again. We have greeted you {$result->num} times.</p>");
        } else {
            $response->getBody()->write('This is our first time meeting you!');
        }
    }
    
    return $response;
});

$app->run();