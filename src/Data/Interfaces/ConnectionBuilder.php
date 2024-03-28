<?php

namespace App\Data\Interfaces;

use PDO;

interface ConnectionBuilder
{

    /**
     * @return PDO
     */
    public function connect(): PDO;

    /**
     * @return array
     */
    public function getConnectionDetails(): array;
}
