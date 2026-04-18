<?php

namespace App\Infra\Cache;

use App\Application\Contracts\ProductCacheInterface;
use Illuminate\Support\Facades\Cache;

class RedisProductCache implements ProductCacheInterface
{
    private const TAG_LIST = 'products_list';
    private const TAG_SHOW = 'products_show';

    public function getList(int $page, int $perPage, ?string $search): ?array
    {
        return Cache::tags(self::TAG_LIST)->get($this->listKey($page, $perPage, $search));
    }

    public function putList(int $page, int $perPage, ?string $search, array $data, int $ttl): void
    {
        Cache::tags(self::TAG_LIST)->put($this->listKey($page, $perPage, $search), $data, $ttl);
    }

    public function getByUuid(string $uuid): ?array
    {
        return Cache::tags(self::TAG_SHOW)->get($uuid);
    }

    public function putByUuid(string $uuid, array $data, int $ttl): void
    {
        Cache::tags(self::TAG_SHOW)->put($uuid, $data, $ttl);
    }

    public function invalidateAll(): void
    {
        Cache::tags(self::TAG_LIST)->flush();
    }

    public function invalidateByUuid(string $uuid): void
    {
        Cache::tags(self::TAG_SHOW)->forget($uuid);
        $this->invalidateAll();
    }

    private function listKey(int $page, int $perPage, ?string $search): string
    {
        return $page . ':' . $perPage . ':' . md5($search ?? '');
    }
}
