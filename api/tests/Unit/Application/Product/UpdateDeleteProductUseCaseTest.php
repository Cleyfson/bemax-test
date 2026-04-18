<?php

namespace Tests\Unit\Application\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\ProductNotifierInterface;
use App\Application\Contracts\SlugGeneratorInterface;
use App\Application\UseCases\Product\DeleteProductUseCase;
use App\Application\UseCases\Product\UpdateProductUseCase;
use App\Domain\Entities\CategoryEntity;
use App\Domain\Entities\ProductEntity;
use App\Domain\Exceptions\ProductNotFoundException;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use PHPUnit\Framework\TestCase;

class UpdateDeleteProductUseCaseTest extends TestCase
{
    private function makeProduct(string $uuid = 'prod-uuid'): ProductEntity
    {
        return ProductEntity::fromArray([
            'uuid'     => $uuid,
            'name'     => 'Product',
            'slug'     => 'product',
            'price'    => 10.0,
            'category' => ['uuid' => 'cat-uuid', 'name' => 'Cat'],
            'tags'     => [],
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_update_returns_updated_product(): void
    {
        $original = $this->makeProduct();
        $updated  = ProductEntity::fromArray([
            'uuid' => 'prod-uuid', 'name' => 'Updated', 'slug' => 'updated',
            'price' => 20.0, 'category' => ['uuid' => 'cat-uuid', 'name' => 'Cat'], 'tags' => [],
        ]);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($original);
        $repo->method('update')->willReturn($updated);

        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $tagRepo      = $this->createMock(TagRepositoryInterface::class);
        $tagRepo->method('findByUuids')->willReturn([]);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->method('generate')->willReturn('updated');

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->expects($this->once())->method('invalidateAll');
        $cache->expects($this->once())->method('invalidateByUuid')->with('prod-uuid');

        $useCase = new UpdateProductUseCase($repo, $categoryRepo, $tagRepo, $slugGen, $cache);
        $result  = $useCase->execute('prod-uuid', name: 'Updated', price: 20.0);

        $this->assertSame('Updated', $result->getName());
    }

    public function test_update_throws_not_found_for_unknown_uuid(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn(null);

        $useCase = new UpdateProductUseCase(
            $repo,
            $this->createMock(CategoryRepositoryInterface::class),
            $this->createMock(TagRepositoryInterface::class),
            $this->createMock(SlugGeneratorInterface::class),
            $this->createMock(ProductCacheInterface::class),
        );

        $useCase->execute('non-existent');
    }

    public function test_update_does_not_regenerate_slug_when_unchanged(): void
    {
        $original = $this->makeProduct();

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($original);
        $repo->method('update')->willReturn($original);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->expects($this->never())->method('generate');

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('invalidateAll');
        $cache->method('invalidateByUuid');

        $useCase = new UpdateProductUseCase(
            $repo,
            $this->createMock(CategoryRepositoryInterface::class),
            $this->createMock(TagRepositoryInterface::class),
            $slugGen,
            $cache,
        );

        // Only updating price — slug should NOT be regenerated
        $useCase->execute('prod-uuid', price: 99.0);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_delete_calls_repository_delete(): void
    {
        $product = $this->makeProduct();

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($product);
        $repo->expects($this->once())->method('delete')->with('prod-uuid');

        $cache    = $this->createMock(ProductCacheInterface::class);
        $cache->method('invalidateAll');
        $cache->method('invalidateByUuid');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->method('notifyDeleted');

        $useCase = new DeleteProductUseCase($repo, $cache, $notifier);
        $useCase->execute('prod-uuid');
    }

    public function test_delete_invalidates_cache(): void
    {
        $product = $this->makeProduct();

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($product);
        $repo->method('delete');

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->expects($this->once())->method('invalidateAll');
        $cache->expects($this->once())->method('invalidateByUuid')->with('prod-uuid');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->method('notifyDeleted');

        $useCase = new DeleteProductUseCase($repo, $cache, $notifier);
        $useCase->execute('prod-uuid');
    }

    public function test_delete_dispatches_notification(): void
    {
        $product = $this->makeProduct();

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn($product);
        $repo->method('delete');

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('invalidateAll');
        $cache->method('invalidateByUuid');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->expects($this->once())->method('notifyDeleted');

        $useCase = new DeleteProductUseCase($repo, $cache, $notifier);
        $useCase->execute('prod-uuid');
    }

    public function test_delete_throws_not_found_for_unknown_uuid(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('findByUuid')->willReturn(null);

        $useCase = new DeleteProductUseCase(
            $repo,
            $this->createMock(ProductCacheInterface::class),
            $this->createMock(ProductNotifierInterface::class),
        );

        $useCase->execute('non-existent');
    }
}
