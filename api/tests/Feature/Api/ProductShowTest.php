<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_show_returns_product_by_uuid(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->getJson("/api/products/{$product->uuid}")
            ->assertStatus(200)
            ->assertJsonPath('data.uuid', $product->uuid);
    }

    public function test_show_returns_404_for_unknown_uuid(): void
    {
        $this->getJson('/api/products/non-existent-uuid')
            ->assertStatus(404);
    }

    public function test_show_returns_404_for_soft_deleted_product(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);
        $product->delete();

        $this->getJson("/api/products/{$product->uuid}")
            ->assertStatus(404);
    }

    public function test_show_never_exposes_numeric_id(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->uuid}");
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('id', $response->json('data'));
    }

    public function test_second_show_request_hits_cache(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        // First request — populates cache
        $this->getJson("/api/products/{$product->uuid}")->assertStatus(200);

        // Force delete from DB — cache should still serve data
        Product::withTrashed()->where('uuid', $product->uuid)->forceDelete();

        $this->getJson("/api/products/{$product->uuid}")->assertStatus(200);
    }
}
