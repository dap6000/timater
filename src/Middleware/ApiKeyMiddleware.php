<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\UsersModel;
use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;

/**
 *
 */
class ApiKeyMiddleware implements MiddlewareInterface
{
    /**
     * @param PDO $pdo
     */
    public function __construct(protected PDO $pdo)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $usersModel = new UsersModel(pdo: $this->pdo);
        $users = $usersModel->getAll();
        $user = null;
        $apiKey = $request->getHeaderLine('X-Api-Key');
        if (!$apiKey) {
            throw new HttpUnauthorizedException($request);
        }
        foreach ($users as $u) {
            // This is inefficient on purpose. We currently load all users into
            // the array. And for how I'm testing that's only 3. If this grew
            // to thousands of users this could become a performance bottleneck
            // that runs on every API request. If that ever happened we could
            // switch to $usersModel->getByKey($apiKey) instead.
            if (hash_equals(known_string: $u->apiKey, user_string: $apiKey)) {
                $user = $u;
            }
        }
        if (is_null($user)) {
            throw new HttpForbiddenException($request);
        }
        $body = (array)$request->getParsedBody();
        $body['user'] = $user->toArray();
        return $handler->handle($request->withParsedBody($body));
    }
}
