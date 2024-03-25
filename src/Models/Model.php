<?php

namespace App\Models;

use PDO;

class Model
{
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = DB::makeConnection();
    }

}