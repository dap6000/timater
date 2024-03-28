<?php

declare(strict_types=1);

namespace App\Models;

use Monolog\Logger;
use PDO;

/**
 *
 */
class Model
{
    /**
     * @param PDO $pdo
     */
    public function __construct(protected PDO $pdo)
    {
    }
}
