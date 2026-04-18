<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductStoreTest extends TestCase
{
    use DatabaseTransactions;

    private function payload(array $overrides = []): array
    {
        $category = Category::factory()->create();

        return array_merge([
            'name'          => 'New Product',
            'price'         => 49.99,
            'category_uuid' => $category->uuid,
        ], $overrides);
    }

    public function test_store_creates_product_and_returns_201(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/products', $this->payload());

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['uuid', 'name', 'slug', 'price', 'category', 'tags']]);

        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }

    public function test_store_generates_slug_when_not_provided(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/products', $this->payload(['name' => 'Auto Slug Product']));

        $response->assertStatus(201);
        $this->assertSame('auto-slug-product', $response->json('data.slug'));
    }

    public function test_store_uses_provided_slug(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/products', $this->payload(['slug' => 'custom-slug']));

        $response->assertStatus(201);
        $this->assertSame('custom-slug', $response->json('data.slug'));
    }

    public function test_store_creates_product_without_tags(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/products', $this->payload());

        $response->assertStatus(201);
        $this->assertSame([], $response->json('data.tags'));
    }

    public function test_store_attaches_tags(): void
    {
        Mail::fake();

        $tag      = Tag::factory()->create();
        $response = $this->postJson('/api/products', $this->payload(['tag_uuids' => [$tag->uuid]]));

        $response->assertStatus(201);
        $this->assertCount(1, $response->json('data.tags'));
        $this->assertSame($tag->uuid, $response->json('data.tags.0.uuid'));
    }

    public function test_store_returns_422_for_missing_required_fields(): void
    {
        $this->postJson('/api/products', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category_uuid']);
    }

    public function test_store_returns_422_for_negative_price(): void
    {
        $this->postJson('/api/products', $this->payload(['price' => -1]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_store_returns_422_for_invalid_category_uuid(): void
    {
        $response = $this->postJson('/api/products', [
            'name'          => 'Product',
            'price'         => 10.0,
            'category_uuid' => 'non-existent-uuid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_uuid']);

        // Validation error must not contain numeric id
        $errors = json_encode($response->json('errors'));
        $this->assertStringNotContainsString('"id"', $errors);
    }

    public function test_store_invalidates_cache(): void
    {
        Mail::fake();

        $category = Category::factory()->create();

        // Populate cache via index
        $this->getJson('/api/products')->assertStatus(200);

        // Create new product
        $this->postJson('/api/products', [
            'name'          => 'Cache Buster',
            'price'         => 5.0,
            'category_uuid' => $category->uuid,
        ])->assertStatus(201);

        // Cache should be invalidated — new product must appear
        $response = $this->getJson('/api/products');
        $names    = array_column($response->json('data'), 'name');
        $this->assertContains('Cache Buster', $names);
    }
}
