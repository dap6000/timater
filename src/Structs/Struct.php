<?php

namespace App\Structs;

interface Struct
{
    public static function fromRow(array $row): self;
}