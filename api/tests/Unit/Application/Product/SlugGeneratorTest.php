<?php

namespace Tests\Unit\Application\Product;

use App\Infra\Slug\EloquentSlugGenerator;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    private EloquentSlugGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new EloquentSlugGenerator();
    }

    public function test_generates_slug_from_name_when_not_provided(): void
    {
        $slug = $this->generator->generate('My Cool Product');
        $this->assertSame('my-cool-product', $slug);
    }

    public function test_uses_provided_slug(): void
    {
        $slug = $this->generator->generate('Product Name', 'custom-slug');
        $this->assertSame('custom-slug', $slug);
    }

    public function test_adds_numeric_suffix_when_slug_exists(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['slug' => 'existing-slug', 'category_id' => $category->id]);

        $slug = $this->generator->generate('Existing Slug');
        $this->assertSame('existing-slug-1', $slug);
    }

    public function test_increments_suffix_until_unique(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['slug' => 'test-slug',   'category_id' => $category->id]);
        Product::factory()->create(['slug' => 'test-slug-1', 'category_id' => $category->id]);
        Product::factory()->create(['slug' => 'test-slug-2', 'category_id' => $category->id]);

        $slug = $this->generator->generate('Test Slug');
        $this->assertSame('test-slug-3', $slug);
    }

    public function test_soft_deleted_record_does_not_block_uniqueness(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['slug' => 'deleted-slug', 'category_id' => $category->id]);
        $product->delete(); // soft delete

        $slug = $this->generator->generate('Deleted Slug');
        $this->assertSame('deleted-slug', $slug);
    }

    public function test_adds_suffix_when_explicit_slug_already_exists(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['slug' => 'taken-slug', 'category_id' => $category->id]);

        $slug = $this->generator->generate('Any Name', 'taken-slug');
        $this->assertSame('taken-slug-1', $slug);
    }

    public function test_excludes_current_product_on_update(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['slug' => 'my-slug', 'category_id' => $category->id]);

        // Updating the same product with the same slug should not add suffix
        $slug = $this->generator->generate('My Slug', 'my-slug', $product->uuid);
        $this->assertSame('my-slug', $slug);
    }
}
