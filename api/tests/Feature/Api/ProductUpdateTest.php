<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_update_returns_200_with_updated_data(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->patchJson("/api/products/{$product->uuid}", ['price' => 199.99])
            ->assertStatus(200)
            ->assertJsonPath('data.price', 199.99);
    }

    public function test_update_returns_404_for_unknown_uuid(): void
    {
        $this->patchJson('/api/products/non-existent', ['price' => 10])
            ->assertStatus(404);
    }

    public function test_update_regenerates_slug_when_name_changes(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create([
            'name'        => 'Old Name',
            'slug'        => 'old-name',
            'category_id' => $category->id,
        ]);

        $response = $this->patchJson("/api/products/{$product->uuid}", ['name' => 'Brand New Name']);

        $response->assertStatus(200);
        $this->assertSame('brand-new-name', $response->json('data.slug'));
    }

    public function test_update_slug_does_not_conflict_with_itself(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create([
            'name'        => 'Same Name',
            'slug'        => 'same-name',
            'category_id' => $category->id,
        ]);

        // Updating name to the same value — should keep slug without suffix
        $response = $this->patchJson("/api/products/{$product->uuid}", ['name' => 'Same Name']);

        $response->assertStatus(200);
        $this->assertSame('same-name', $response->json('data.slug'));
    }

    public function test_update_can_reuse_slug_of_soft_deleted_product(): void
    {
        $category = Category::factory()->create();

        $deleted = Product::factory()->create([
            'name'        => 'Deleted Product',
            'slug'        => 'deleted-slug',
            'category_id' => $category->id,
        ]);
        $deleted->delete();

        $active = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->patchJson("/api/products/{$active->uuid}", ['slug' => 'deleted-slug']);

        $response->assertStatus(200);
        $this->assertSame('deleted-slug', $response->json('data.slug'));
    }

    public function test_update_returns_422_for_negative_price(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->patchJson("/api/products/{$product->uuid}", ['price' => -5])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }
}
