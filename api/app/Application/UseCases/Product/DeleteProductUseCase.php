<?php

namespace App\Application\UseCases\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\ProductNotifierInterface;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;

class DeleteProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductCacheInterface      $cache,
        private readonly ProductNotifierInterface   $notifier,
    ) {}

    public function execute(string $uuid): void
    {
        $product = $this->productRepository->findByUuid($uuid);

        if ($product === null) {
            throw new ProductNotFoundException($uuid);
        }

        $this->productRepository->delete($uuid);

        $this->cache->invalidateAll();
        $this->cache->invalidateByUuid($uuid);

        $this->notifier->notifyDeleted($product);
    }
}
