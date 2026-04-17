<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\CategoryEntity;

interface CategoryRepositoryInterface
{
    public function findByUuid(string $uuid): ?CategoryEntity;
}
