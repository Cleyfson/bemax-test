<?php

namespace App\Infra\Repositories;

use App\Domain\Entities\CategoryEntity;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findByUuid(string $uuid): ?CategoryEntity
    {
        $model = Category::where('uuid', $uuid)->first();

        if (!$model) {
            return null;
        }

        return CategoryEntity::fromArray([
            'uuid' => $model->uuid,
            'name' => $model->name,
        ]);
    }
}
