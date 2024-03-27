<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 *
 */
class Model
{
    protected PDO $pdo;

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
