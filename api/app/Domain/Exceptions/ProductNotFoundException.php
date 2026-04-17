<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class ProductNotFoundException extends RuntimeException
{
    public function __construct(string $uuid)
    {
        parent::__construct("Product with UUID [{$uuid}] not found.");
    }
}
