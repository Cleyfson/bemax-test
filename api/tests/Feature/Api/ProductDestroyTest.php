<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_destroy_returns_204_and_soft_deletes(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->deleteJson("/api/products/{$product->uuid}")
            ->assertStatus(204);

        $this->assertSoftDeleted('products', ['uuid' => $product->uuid]);
    }

    public function test_destroy_returns_404_for_unknown_uuid(): void
    {
        $this->deleteJson('/api/products/non-existent')
            ->assertStatus(404);
    }

    public function test_destroy_returns_404_for_already_deleted_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        $product->delete();

        $this->deleteJson("/api/products/{$product->uuid}")
            ->assertStatus(404);
    }

    public function test_destroy_invalidates_cache(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        // Populate cache
        $this->getJson('/api/products')->assertStatus(200);
        $this->getJson("/api/products/{$product->uuid}")->assertStatus(200);

        // Delete
        $this->deleteJson("/api/products/{$product->uuid}")->assertStatus(204);

        // Index must not return deleted product
        $response = $this->getJson('/api/products');
        $uuids    = array_column($response->json('data'), 'uuid');
        $this->assertNotContains($product->uuid, $uuids);
    }

    public function test_slug_of_deleted_product_can_be_reused(): void
    {
        Mail::fake();

        $category = Category::factory()->create();
        $deleted  = Product::factory()->create([
            'name'        => 'Reusable Slug',
            'slug'        => 'reusable-slug',
            'category_id' => $category->id,
        ]);
        $deleted->delete();

        $response = $this->postJson('/api/products', [
            'name'          => 'Reusable Slug',
            'slug'          => 'reusable-slug',
            'price'         => 10.0,
            'category_uuid' => $category->uuid,
        ]);

        $response->assertStatus(201);
        $this->assertSame('reusable-slug', $response->json('data.slug'));
    }
}
