<?php

namespace App\Infra\Cache;

use App\Application\Contracts\ProductCacheInterface;
use Illuminate\Support\Facades\Redis;

class RedisProductCache implements ProductCacheInterface
{
    private const PREFIX_LIST = 'products:list:';
    private const PREFIX_SHOW = 'products:show:';

    public function getList(int $page, int $perPage, ?string $search): ?array
    {
        $raw = Redis::get($this->listKey($page, $perPage, $search));

        return $raw !== null ? json_decode($raw, true) : null;
    }

    public function putList(int $page, int $perPage, ?string $search, array $data, int $ttl): void
    {
        Redis::setex($this->listKey($page, $perPage, $search), $ttl, json_encode($data));
    }

    public function getByUuid(string $uuid): ?array
    {
        $raw = Redis::get(self::PREFIX_SHOW . $uuid);

        return $raw !== null ? json_decode($raw, true) : null;
    }

    public function putByUuid(string $uuid, array $data, int $ttl): void
    {
        Redis::setex(self::PREFIX_SHOW . $uuid, $ttl, json_encode($data));
    }

    public function invalidateAll(): void
    {
        $keys = Redis::keys(self::PREFIX_LIST . '*');

        if (!empty($keys)) {
            Redis::del($keys);
        }
    }

    public function invalidateByUuid(string $uuid): void
    {
        Redis::del(self::PREFIX_SHOW . $uuid);
        $this->invalidateAll();
    }

    private function listKey(int $page, int $perPage, ?string $search): string
    {
        return self::PREFIX_LIST . $page . ':' . $perPage . ':' . md5($search ?? '');
    }
}
