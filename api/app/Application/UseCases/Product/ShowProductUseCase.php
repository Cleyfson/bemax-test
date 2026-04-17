<?php

namespace App\Application\UseCases\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Domain\Entities\ProductEntity;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

class ShowProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductCacheInterface      $cache,
    ) {}

    public function execute(string $uuid): ProductEntity
    {
        $cached = $this->cache->getByUuid($uuid);

        if ($cached !== null) {
            return ProductEntity::fromArray($cached);
        }

        $product = $this->productRepository->findByUuid($uuid);

        if ($product === null) {
            throw new ProductNotFoundException($uuid);
        }

        $ttl = config('cache.products_ttl', 3600);
        $this->cache->putByUuid($uuid, $product->jsonSerialize(), $ttl);

        return $product;
    }
}
