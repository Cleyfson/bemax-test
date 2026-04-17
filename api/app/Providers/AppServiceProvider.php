<?php

namespace App\Providers;

use App\Application\Contracts\ProductCacheInterface;
use App\Application\Contracts\ProductNotifierInterface;
use App\Application\Contracts\SlugGeneratorInterface;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infra\Cache\RedisProductCache;
use App\Infra\Notifications\LaravelProductNotifier;
use App\Infra\Repositories\EloquentCategoryRepository;
use App\Infra\Repositories\EloquentProductRepository;
use App\Infra\Repositories\EloquentTagRepository;
use App\Infra\Slug\EloquentSlugGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, EloquentTagRepository::class);
        $this->app->bind(SlugGeneratorInterface::class, EloquentSlugGenerator::class);
        $this->app->bind(ProductCacheInterface::class, RedisProductCache::class);
        $this->app->bind(ProductNotifierInterface::class, LaravelProductNotifier::class);
    }

    public function boot(): void
    {
        //
    }
}
