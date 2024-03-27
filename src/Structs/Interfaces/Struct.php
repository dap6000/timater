<?php

declare(strict_types=1);

namespace App\Structs\Interfaces;

/**
 *
 */
interface Struct
{
    /**
     * @param array $row
     * @return self
     */
    public static function fromRow(array $row): self;

    /**
     * @return array
     */
    public function toArray(): array;
}
