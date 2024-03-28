<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Interfaces\ConnectionBuilder;
use App\Data\Traits\Connectable;
use Exception;
use PDO;

/**
 *
 */
class AppConnectionBuilder implements ConnectionBuilder
{
    use Connectable;

    /**
     * @return PDO
     * @throws Exception
     */
    public function connect(): PDO
    {
        list($dsn, $user, $password) = $this->getConnectionDetails();

        return $this->makePdo($dsn, $user, $password);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getConnectionDetails(): array
    {
        $host = getenv('MYSQL_APP_HOST');
        $name = getenv('MYSQL_APP_DB');
        $user = getenv('MYSQL_APP_USER');
        $password = getenv('MYSQL_APP_PASS');
        if (
            $host === false
            || $name === false
            || $user === false
            || $password === false
        ) {
            throw new Exception(message: 'Environment improperly configured.');
        }
        $dsn = "mysql:host=$host;dbname=$name;charset=UTF8";

        return [$dsn, $user, $password];
    }
}
