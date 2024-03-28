<?php

declare(strict_types=1);

namespace App\Structs;

use App\Structs\Exceptions\InvalidUserRoleException;
use App\Structs\Interfaces\Struct;

/**
 *
 */
final readonly class User implements Struct
{
    public const array ROLES = ['Standard', 'Admin'];

    /**
     * @param int|null $id
     * @param string $username
     * @param string $email
     * @param string $role
     * @param string $apiKey
     */
    public function __construct(
        public ?int $id,
        public string $username,
        public string $email,
        public string $role,
        public string $apiKey,
    ) {
        if (!in_array($this->role, User::ROLES)) {
            throw InvalidUserRoleException::make($this->role);
        }
    }

    /**
     * @param array $row
     * @return self
     */
    public static function fromRow(array $row): self
    {
        return new User(
            $row['id'],
            $row['username'],
            $row['email'],
            $row['role'],
            $row['api_key'],
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            // No reason to pass around api keys outside of header info
        ];
    }
}
