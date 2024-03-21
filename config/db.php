<?php

function db(): ?PDO {

    $pdo = null;
    $db_host = 'database';
    $db_name = 'greetings';
    $db_user = getenv('MYSQL_APP_USER');
    $db_password = getenv('MYSQL_APP_PASS');
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=UTF8";

    try {
        $pdo = new PDO($dsn, $db_user, $db_password);
        // TODO log successful connection
    } catch (PDOException $e) {
        // TODO log exception
    }
    return $pdo;
}
