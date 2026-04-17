<?php

namespace App\Infra\Slug;

use App\Application\Contracts\SlugGeneratorInterface;
use App\Models\Product;
use Illuminate\Support\Str;

class EloquentSlugGenerator implements SlugGeneratorInterface
{
    public function generate(
        string $name,
        ?string $providedSlug = null,
        ?string $excludeUuid = null
    ): string {
        $base = $providedSlug !== null && trim($providedSlug) !== ''
            ? Str::slug($providedSlug)
            : Str::slug($name);

        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $excludeUuid)) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?string $excludeUuid): bool
    {
        // Query WITHOUT withTrashed — soft-deleted records do not block uniqueness
        $query = Product::where('slug', $slug);

        if ($excludeUuid !== null) {
            $query->where('uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }
}
