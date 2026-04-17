<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\TagEntity;

interface TagRepositoryInterface
{
    /** @return TagEntity[] */
    public function findByUuids(array $uuids): array;

    /** @return int[] */
    public function getIdsByUuids(array $uuids): array;
}
