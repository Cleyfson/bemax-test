<?php

namespace App\Application\Contracts;

interface ProductCacheInterface
{
    public function getList(int $page, int $perPage, ?string $search): ?array;

    public function putList(int $page, int $perPage, ?string $search, array $data, int $ttl): void;

    public function getByUuid(string $uuid): ?array;

    public function putByUuid(string $uuid, array $data, int $ttl): void;

    public function invalidateAll(): void;

    public function invalidateByUuid(string $uuid): void;
}
