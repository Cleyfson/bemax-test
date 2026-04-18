<?php

namespace Tests\Unit\Application\Product;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\ProductNotifierInterface;
use App\Application\Contracts\SlugGeneratorInterface;
use App\Application\UseCases\Product\CreateProductUseCase;
use App\Domain\Entities\CategoryEntity;
use App\Domain\Entities\ProductEntity;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateProductUseCaseTest extends TestCase
{
    private function makeCategoryEntity(): CategoryEntity
    {
        return CategoryEntity::fromArray(['uuid' => 'cat-uuid', 'name' => 'Books']);
    }

    private function makeProductEntity(): ProductEntity
    {
        return ProductEntity::fromArray([
            'uuid'        => 'prod-uuid',
            'name'        => 'New Product',
            'slug'        => 'new-product',
            'price'       => 29.99,
            'description' => null,
            'category'    => ['uuid' => 'cat-uuid', 'name' => 'Books'],
            'tags'        => [],
        ]);
    }

    private function makeUseCase(
        ?ProductRepositoryInterface  $repo = null,
        ?CategoryRepositoryInterface $categoryRepo = null,
        ?TagRepositoryInterface      $tagRepo = null,
        ?SlugGeneratorInterface      $slugGen = null,
        ?ProductCacheInterface       $cache = null,
        ?ProductNotifierInterface    $notifier = null,
    ): CreateProductUseCase {
        $repo        ??= $this->createMock(ProductRepositoryInterface::class);
        $categoryRepo ??= $this->createMock(CategoryRepositoryInterface::class);
        $tagRepo     ??= $this->createMock(TagRepositoryInterface::class);
        $slugGen     ??= $this->createMock(SlugGeneratorInterface::class);
        $cache       ??= $this->createMock(ProductCacheInterface::class);
        $notifier    ??= $this->createMock(ProductNotifierInterface::class);

        return new CreateProductUseCase($repo, $categoryRepo, $tagRepo, $slugGen, $cache, $notifier);
    }

    public function test_creates_product_with_provided_slug(): void
    {
        $category = $this->makeCategoryEntity();
        $product  = $this->makeProductEntity();

        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('findByUuid')->with('cat-uuid')->willReturn($category);

        $tagRepo = $this->createMock(TagRepositoryInterface::class);
        $tagRepo->method('findByUuids')->willReturn([]);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->method('generate')->with('New Product', 'new-product')->willReturn('new-product');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('create')->willReturn($product);

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->expects($this->once())->method('invalidateAll');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->expects($this->once())->method('notifyCreated');

        $useCase = $this->makeUseCase($repo, $categoryRepo, $tagRepo, $slugGen, $cache, $notifier);
        $result  = $useCase->execute('New Product', 29.99, 'cat-uuid', 'new-product');

        $this->assertSame('prod-uuid', $result->getUuid());
    }

    public function test_generates_slug_automatically_when_not_provided(): void
    {
        $category = $this->makeCategoryEntity();
        $product  = $this->makeProductEntity();

        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('findByUuid')->willReturn($category);

        $tagRepo = $this->createMock(TagRepositoryInterface::class);
        $tagRepo->method('findByUuids')->willReturn([]);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->expects($this->once())
            ->method('generate')
            ->with('New Product', null)
            ->willReturn('new-product');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('create')->willReturn($product);

        $useCase = $this->makeUseCase($repo, $categoryRepo, $tagRepo, $slugGen);
        $useCase->execute('New Product', 29.99, 'cat-uuid');
    }

    public function test_invalidates_cache_after_creation(): void
    {
        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('findByUuid')->willReturn($this->makeCategoryEntity());

        $tagRepo = $this->createMock(TagRepositoryInterface::class);
        $tagRepo->method('findByUuids')->willReturn([]);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->method('generate')->willReturn('slug');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('create')->willReturn($this->makeProductEntity());

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->expects($this->once())->method('invalidateAll');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->method('notifyCreated');

        $useCase = $this->makeUseCase($repo, $categoryRepo, $tagRepo, $slugGen, $cache, $notifier);
        $useCase->execute('New Product', 29.99, 'cat-uuid');
    }

    public function test_dispatches_notification_after_creation(): void
    {
        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('findByUuid')->willReturn($this->makeCategoryEntity());

        $tagRepo = $this->createMock(TagRepositoryInterface::class);
        $tagRepo->method('findByUuids')->willReturn([]);

        $slugGen = $this->createMock(SlugGeneratorInterface::class);
        $slugGen->method('generate')->willReturn('slug');

        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->method('create')->willReturn($this->makeProductEntity());

        $cache = $this->createMock(ProductCacheInterface::class);
        $cache->method('invalidateAll');

        $notifier = $this->createMock(ProductNotifierInterface::class);
        $notifier->expects($this->once())->method('notifyCreated');

        $useCase = $this->makeUseCase($repo, $categoryRepo, $tagRepo, $slugGen, $cache, $notifier);
        $useCase->execute('New Product', 29.99, 'cat-uuid');
    }
}
