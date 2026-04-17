<?php

namespace App\Application\Contracts;

use App\Domain\Entities\ProductEntity;

interface ProductNotifierInterface
{
    public function notifyCreated(ProductEntity $product): void;

    public function notifyDeleted(ProductEntity $product): void;
}
