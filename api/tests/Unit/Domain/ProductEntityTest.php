<?php

namespace Tests\Unit\Domain;

use App\Domain\Entities\CategoryEntity;
use App\Domain\Entities\ProductEntity;
use App\Domain\Entities\TagEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProductEntityTest extends TestCase
{
    private function makeCategory(): CategoryEntity
    {
        return CategoryEntity::fromArray(['uuid' => 'cat-uuid', 'name' => 'Electronics']);
    }

    private function makeEntity(array $overrides = []): ProductEntity
    {
        return new ProductEntity(
            uuid:        $overrides['uuid']        ?? 'prod-uuid',
            name:        $overrides['name']        ?? 'Test Product',
            slug:        $overrides['slug']        ?? 'test-product',
            price:       $overrides['price']       ?? 10.0,
            description: $overrides['description'] ?? null,
            category:    $overrides['category']    ?? $this->makeCategory(),
            tags:        $overrides['tags']        ?? [],
        );
    }

    public function test_setName_throws_on_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeEntity(['name' => '']);
    }

    public function test_setName_throws_on_whitespace_only(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeEntity(['name' => '   ']);
    }

    public function test_setName_throws_when_exceeds_255_chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeEntity(['name' => str_repeat('a', 256)]);
    }

    public function test_setPrice_throws_on_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeEntity(['price' => -0.01]);
    }

    public function test_setPrice_accepts_zero(): void
    {
        $entity = $this->makeEntity(['price' => 0.0]);
        $this->assertSame(0.0, $entity->getPrice());
    }

    public function test_fromArray_hydrates_all_fields(): void
    {
        $tag = TagEntity::fromArray(['uuid' => 'tag-uuid', 'name' => 'Sale']);

        $entity = ProductEntity::fromArray([
            'uuid'        => 'abc-123',
            'name'        => 'My Product',
            'slug'        => 'my-product',
            'price'       => 49.99,
            'description' => 'A description',
            'category'    => ['uuid' => 'cat-uuid', 'name' => 'Books'],
            'tags'        => [['uuid' => 'tag-uuid', 'name' => 'Sale']],
            'created_at'  => '2026-01-01T00:00:00+00:00',
            'updated_at'  => '2026-01-02T00:00:00+00:00',
        ]);

        $this->assertSame('abc-123', $entity->getUuid());
        $this->assertSame('My Product', $entity->getName());
        $this->assertSame('my-product', $entity->getSlug());
        $this->assertSame(49.99, $entity->getPrice());
        $this->assertSame('A description', $entity->getDescription());
        $this->assertSame('cat-uuid', $entity->getCategory()->getUuid());
        $this->assertCount(1, $entity->getTags());
        $this->assertSame('tag-uuid', $entity->getTags()[0]->getUuid());
        $this->assertSame('2026-01-01T00:00:00+00:00', $entity->getCreatedAt());
    }

    public function test_jsonSerialize_never_contains_id(): void
    {
        $data = $this->makeEntity()->jsonSerialize();
        $this->assertArrayNotHasKey('id', $data);
    }

    public function test_jsonSerialize_contains_expected_keys(): void
    {
        $data = $this->makeEntity()->jsonSerialize();
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArrayHasKey('tags', $data);
    }
}
