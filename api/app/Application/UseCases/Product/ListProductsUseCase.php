<?php

namespace App\Application\UseCases\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Domain\Entities\ProductEntity;
use App\Domain\Repositories\ProductRepositoryInterface;

class ListProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductCacheInterface      $cache,
    ) {}

    public function execute(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $cached = $this->cache->getList($page, $perPage, $search);

        if ($cached !== null) {
            return $cached;
        }

        $result = $this->productRepository->findAll($page, $perPage, $search);

        $ttl = config('cache.products_ttl', 3600);
        $this->cache->putList($page, $perPage, $search, $result, $ttl);

        return $result;
    }
}
