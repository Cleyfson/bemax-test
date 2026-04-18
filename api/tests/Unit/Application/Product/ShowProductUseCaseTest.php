<?php

namespace Tests\Unit\Application\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\UseCases\Product\ShowProductUseCase;
use App\Domain\Entities\ProductEntity;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\ProductRepositoryInterface;
use Tests\TestCase;

class ShowProductUseCaseTest extends TestCase
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
        $cache->method('getByUuid')->with('prod-uuid')->willReturn($product->jsonSerialize());

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->never())->method('findByUuid');

        $useCase = new ShowProductUseCase($repo, $cache);
        $result  = $useCase->execute('prod-uuid');

        $this->assertSame('prod-uuid', $result->getUuid());
    }

    public function test_calls_repository_and_populates_cache_on_miss(): void
    {
        $product = $this->makeProduct();

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('getByUuid')->willReturn(null);
        $cache->expects($this->once())->method('putByUuid');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($product);

        $useCase = new ShowProductUseCase($repo, $cache);
        $result  = $useCase->execute('prod-uuid');

        $this->assertSame('prod-uuid', $result->getUuid());
    }

    public function test_throws_not_found_for_unknown_uuid(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('getByUuid')->willReturn(null);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn(null);

        $useCase = new ShowProductUseCase($repo, $cache);
        $useCase->execute('non-existent-uuid');
    }
}
