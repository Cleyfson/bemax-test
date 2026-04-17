<?php

namespace App\Infra\Repositories;

use App\Domain\Entities\TagEntity;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Models\Tag;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function findByUuids(array $uuids): array
    {
        return Tag::whereIn('uuid', $uuids)
            ->get()
            ->map(fn(Tag $tag) => TagEntity::fromArray([
                'uuid' => $tag->uuid,
                'name' => $tag->name,
            ]))
            ->values()
            ->all();
    }

    public function getIdsByUuids(array $uuids): array
    {
        return Tag::whereIn('uuid', $uuids)
            ->pluck('id')
            ->all();
    }
}
