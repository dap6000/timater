<?php

namespace App\Models;

use \PDO;
class DB
{
    public static function makeConnection(): PDO
    {
        $db_host = getenv('MYSQL_HOST');
        $db_name = getenv('MYSQL_APP_DB');
        $db_user = getenv('MYSQL_APP_USER');
        $db_password = getenv('MYSQL_APP_PASS');
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";
        $conn = new PDO($dsn, $db_user, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}