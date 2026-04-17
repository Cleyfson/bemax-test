<?php

namespace App\Application\UseCases\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\SlugGeneratorInterface;
use App\Domain\Entities\ProductEntity;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;

class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface  $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly TagRepositoryInterface      $tagRepository,
        private readonly SlugGeneratorInterface      $slugGenerator,
        private readonly ProductCacheInterface       $cache,
    ) {}

    public function execute(
        string  $uuid,
        ?string $name = null,
        ?float  $price = null,
        ?string $categoryUuid = null,
        ?string $slug = null,
        ?string $description = null,
        ?array  $tagUuids = null,
    ): ProductEntity {
        $current = $this->productRepository->findByUuid($uuid);

        if ($current === null) {
            throw new ProductNotFoundException($uuid);
        }

        $newName  = $name  ?? $current->getName();
        $newPrice = $price ?? $current->getPrice();
        $newDesc  = $description !== null ? (trim($description) === '' ? null : $description) : $current->getDescription();

        // Regenerate slug only if name or slug explicitly changed
        $newSlug = ($name !== null || $slug !== null)
            ? $this->slugGenerator->generate($newName, $slug, $uuid)
            : $current->getSlug();

        $category = $categoryUuid
            ? $this->categoryRepository->findByUuid($categoryUuid)
            : $current->getCategory();

        $tags = $tagUuids !== null
            ? $this->tagRepository->findByUuids($tagUuids)
            : $current->getTags();

        $entity = new ProductEntity(
            uuid: $uuid,
            name: $newName,
            slug: $newSlug,
            price: $newPrice,
            description: $newDesc,
            category: $category,
            tags: $tags,
        );

        $updated = $this->productRepository->update($uuid, $entity, $categoryUuid, $tagUuids);

        $this->cache->invalidateAll();
        $this->cache->invalidateByUuid($uuid);

        return $updated;
    }
}
