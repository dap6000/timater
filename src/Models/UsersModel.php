<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\SQL;
use App\Structs\User;
use Exception;
use PDO;

/**
 *
 */
class UsersModel extends Model
{
    /**
     * @return User[]
     * @throws Exception
     */
    public function getAll(): array
    {
        $getAllUsersStmt = $this->pdo->prepare(query: SQL::ALLUSERS);
        $getAllUsersStmt->execute();
        $all = $getAllUsersStmt->fetchAll(mode: PDO::FETCH_ASSOC);

        return (!empty($all))
            ? array_map(
                fn($row): User => User::fromRow($row),
                $all
            )
            : throw new Exception(message: 'Unable to find users.');
    }

    /**
     * @param int $id
     * @return User
     * @throws Exception
     */
    public function get(int $id): User
    {
        $getUserStmt = $this->pdo->prepare(query: SQL::GETUSERBYID);
        $getUserStmt->execute(params: [':id' => $id]);
        $row = $getUserStmt->fetch(mode: PDO::FETCH_ASSOC);

        return ($row !== false)
            ? User::fromRow($row)
            : throw new Exception(message: 'Unable to find requested user.');
    }

    /**
     * @param string $apiKey
     * @return User
     * @throws Exception
     */
    public function getByKey(string $apiKey): User
    {
        $getUserByKeyStmt = $this->pdo->prepare(query: SQL::GETUSERBYKEY);
        $getUserByKeyStmt->execute(params: [':api_key' => $apiKey]);
        $row = $getUserByKeyStmt->fetch(mode: PDO::FETCH_ASSOC);

        return ($row !== false)
            ? User::fromRow($row)
            : throw new Exception(message: 'Unable to find requested user.');
    }
}
