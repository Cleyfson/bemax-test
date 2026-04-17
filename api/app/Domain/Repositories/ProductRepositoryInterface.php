<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\ProductEntity;

interface ProductRepositoryInterface
{
    public function findAll(int $page, int $perPage, ?string $search): array;

    public function findByUuid(string $uuid): ?ProductEntity;

    public function create(ProductEntity $product, string $categoryUuid, array $tagUuids): ProductEntity;

    public function update(string $uuid, ProductEntity $product, ?string $categoryUuid, ?array $tagUuids): ProductEntity;

    public function delete(string $uuid): void;
}
