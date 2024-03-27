<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use PDO;

/**
 * PDO factory class. Should replace with proper dependency injection
 * eventually.
 */
class DB
{
    /**
     * @return PDO
     * @throws Exception
     */
    public static function makeConnection(): PDO
    {
        $db_host = getenv('MYSQL_HOST');
        $db_name = getenv('MYSQL_APP_DB');
        $db_user = getenv('MYSQL_APP_USER');
        $db_password = getenv('MYSQL_APP_PASS');
        if (
            $db_host === false
            || $db_name === false
            || $db_user === false
            || $db_password === false
        ) {
            throw new Exception(message: 'Environment improperly configured.');
        }
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";
        $conn = new PDO($dsn, $db_user, $db_password);
        $conn->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        return $conn;
    }
}
