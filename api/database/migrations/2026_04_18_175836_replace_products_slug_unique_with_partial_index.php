<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropUnique('products_slug_unique'));
        // Functional unique index: active rows are indexed by slug, deleted rows
        // store NULL — MySQL allows duplicate NULLs, so soft-deleted slugs can be reused.
        DB::statement('ALTER TABLE products ADD UNIQUE INDEX products_slug_active_unique ((IF(deleted_at IS NULL, slug, NULL)))');
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropUnique('products_slug_active_unique'));
        Schema::table('products', fn (Blueprint $table) => $table->unique('slug'));
    }
};
