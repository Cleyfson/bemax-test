<?php

namespace App\Application\UseCases\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\ProductNotifierInterface;
use App\Application\Contracts\SlugGeneratorInterface;
use App\Domain\Entities\ProductEntity;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;

class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface  $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly TagRepositoryInterface      $tagRepository,
        private readonly SlugGeneratorInterface      $slugGenerator,
        private readonly ProductCacheInterface       $cache,
        private readonly ProductNotifierInterface    $notifier,
    ) {}

    public function execute(
        string  $name,
        float   $price,
        string  $categoryUuid,
        ?string $slug = null,
        ?string $description = null,
        array   $tagUuids = [],
    ): ProductEntity {
        $category = $this->categoryRepository->findByUuid($categoryUuid);

        if ($category === null) {
            throw new ProductNotFoundException($categoryUuid);
        }

        $generatedSlug = $this->slugGenerator->generate($name, $slug);
        $tagItems      = $this->tagRepository->findByUuids($tagUuids);

        $entity = new ProductEntity(
            uuid: '',
            name: $name,
            slug: $generatedSlug,
            price: $price,
            description: $description,
            category: $category,
            tags: $tagItems,
        );

        $created = $this->productRepository->create($entity, $categoryUuid, $tagUuids);

        $this->cache->invalidateAll();
        $this->notifier->notifyCreated($created);

        return $created;
    }
}
