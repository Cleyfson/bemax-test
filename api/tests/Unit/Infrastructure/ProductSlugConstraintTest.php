<?php

namespace Tests\Unit\Infrastructure;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductSlugConstraintTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(string $slug): Product
    {
        $category = Category::factory()->create();
        return Product::factory()->create(['slug' => $slug, 'category_id' => $category->id]);
    }

    public function test_db_blocks_two_active_products_with_same_slug(): void
    {
        $this->makeProduct('duplicate-slug');

        $this->expectException(UniqueConstraintViolationException::class);

        $this->makeProduct('duplicate-slug');
    }

    public function test_db_allows_active_product_to_reuse_soft_deleted_slug(): void
    {
        $deleted = $this->makeProduct('reusable-slug');
        $deleted->delete(); // soft delete

        // Must NOT throw — the functional index ignores soft-deleted rows
        $active = $this->makeProduct('reusable-slug');

        $this->assertSame('reusable-slug', $active->slug);
    }

    public function test_db_blocks_second_active_product_even_when_soft_deleted_one_exists(): void
    {
        $deleted = $this->makeProduct('shared-slug');
        $deleted->delete();

        $this->makeProduct('shared-slug'); // first active — OK

        $this->expectException(UniqueConstraintViolationException::class);

        $this->makeProduct('shared-slug'); // second active — must fail
    }
}
