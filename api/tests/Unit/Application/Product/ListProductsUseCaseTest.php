<?php

namespace Tests\Unit\Application\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\UseCases\Product\ListProductsUseCase;
use App\Domain\Entities\CategoryEntity;
use App\Domain\Entities\ProductEntity;
use App\Domain\Repositories\ProductRepositoryInterface;
use Tests\TestCase;

class ListProductsUseCaseTest extends TestCase
{
    private function makeProduct(): ProductEntity
    {
        return ProductEntity::fromArray([
            'uuid'     => 'prod-uuid',
            'name'     => 'Product',
            'slug'     => 'product',
            'price'    => 10.0,
            'category' => ['uuid' => 'cat-uuid', 'name' => 'Cat'],
            'tags'     => [],
        ]);
    }

    public function test_returns_from_cache_without_hitting_repository(): void
    {
        $product = $this->makeProduct();

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('getList')->willReturn([
            'data' => [$product->jsonSerialize()],
            'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1, 'last_page' => 1],
        ]);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->never())->method('findAll');

        $useCase = new ListProductsUseCase($repo, $cache);
        $result  = $useCase->execute(1, 15);

        $this->assertCount(1, $result['data']);
        $this->assertInstanceOf(ProductEntity::class, $result['data'][0]);
    }

    public function test_calls_repository_and_populates_cache_on_miss(): void
    {
        $product = $this->makeProduct();

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('getList')->willReturn(null);
        $cache->expects($this->once())->method('putList');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())->method('findAll')->willReturn([
            'data' => [$product],
            'meta' => ['current_page' => 1, 'per_page' => 15, 'total' => 1, 'last_page' => 1],
        ]);

        $useCase = new ListProductsUseCase($repo, $cache);
        $result  = $useCase->execute(1, 15);

        $this->assertCount(1, $result['data']);
    }
}
