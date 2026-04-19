<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_index_returns_200_with_paginated_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['uuid', 'name', 'slug', 'price', 'category', 'tags']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_index_never_exposes_numeric_id(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('id', $response->json('data.0'));
    }

    public function test_index_filters_by_search(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Unique Widget', 'slug' => 'unique-widget', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Other Thing',   'slug' => 'other-thing',   'category_id' => $category->id]);

        $response = $this->getJson('/api/products?search=Widget');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Unique Widget', $response->json('data.0.name'));
    }

    public function test_second_request_hits_cache(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        // First request — populates cache
        $first = $this->getJson('/api/products');
        $first->assertStatus(200);
        $cachedCount = count($first->json('data'));

        // Force-delete directly — bypasses use case so cache is NOT invalidated
        Product::query()->forceDelete();

        // Second request must come from cache (same data, even though DB is now empty)
        $second = $this->getJson('/api/products');
        $second->assertStatus(200);
        $this->assertCount($cachedCount, $second->json('data'));
    }
}
