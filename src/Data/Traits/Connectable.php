<?php

namespace App\Data\Traits;

use PDO;

trait Connectable
{
    /**
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @return PDO
     */
    public function makePdo(string $dsn, string $user, string $password): PDO
    {
        $conn = new PDO($dsn, $user, $password);
        $conn->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        return $conn;
    }
}
