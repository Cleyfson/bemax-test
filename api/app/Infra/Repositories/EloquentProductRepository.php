<?php

namespace App\Infra\Repositories;

use App\Domain\Entities\CategoryEntity;
use App\Domain\Entities\ProductEntity;
use App\Domain\Entities\TagEntity;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findAll(int $page, int $perPage, ?string $search): array
    {
        $query = Product::with(['category', 'tags'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            }));

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data'  => $paginated->map(fn(Product $p) => $this->toEntity($p))->values()->all(),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ];
    }

    public function findByUuid(string $uuid): ?ProductEntity
    {
        $model = Product::with(['category', 'tags'])->where('uuid', $uuid)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(ProductEntity $product, string $categoryUuid, array $tagUuids): ProductEntity
    {
        $categoryId = Category::where('uuid', $categoryUuid)->value('id');
        $tagIds = Tag::whereIn('uuid', $tagUuids)->pluck('id')->all();

        $model = Product::create([
            'name'        => $product->getName(),
            'slug'        => $product->getSlug(),
            'price'       => $product->getPrice(),
            'description' => $product->getDescription(),
            'category_id' => $categoryId,
        ]);

        if (!empty($tagIds)) {
            $model->tags()->attach($tagIds);
        }

        $model->load(['category', 'tags']);

        return $this->toEntity($model);
    }

    public function update(string $uuid, ProductEntity $product, ?string $categoryUuid, ?array $tagUuids): ProductEntity
    {
        $model = Product::where('uuid', $uuid)->firstOrFail();

        $categoryId = $categoryUuid
            ? Category::where('uuid', $categoryUuid)->value('id')
            : null;

        $tagIds = $tagUuids !== null
            ? Tag::whereIn('uuid', $tagUuids)->pluck('id')->all()
            : null;

        $model->update(array_filter([
            'name'        => $product->getName(),
            'slug'        => $product->getSlug(),
            'price'       => $product->getPrice(),
            'description' => $product->getDescription(),
            'category_id' => $categoryId,
        ], fn($v) => $v !== null));

        if ($tagIds !== null) {
            $model->tags()->sync($tagIds);
        }

        $model->load(['category', 'tags']);

        return $this->toEntity($model);
    }

    public function delete(string $uuid): void
    {
        Product::where('uuid', $uuid)->firstOrFail()->delete();
    }

    private function toEntity(Product $model): ProductEntity
    {
        $category = CategoryEntity::fromArray([
            'uuid' => $model->category->uuid,
            'name' => $model->category->name,
        ]);

        $tags = $model->tags->map(fn($tag) => TagEntity::fromArray([
            'uuid' => $tag->uuid,
            'name' => $tag->name,
        ]))->values()->all();

        return ProductEntity::fromArray([
            'uuid'        => $model->uuid,
            'name'        => $model->name,
            'slug'        => $model->slug,
            'price'       => (float) $model->price,
            'description' => $model->description,
            'category'    => $category,
            'tags'        => $tags,
            'created_at'  => $model->created_at?->toIso8601String(),
            'updated_at'  => $model->updated_at?->toIso8601String(),
        ]);
    }
}
