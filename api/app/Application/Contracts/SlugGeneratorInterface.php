<?php

namespace App\Application\Contracts;

interface SlugGeneratorInterface
{
    public function generate(
        string $name,
        ?string $providedSlug = null,
        ?string $excludeUuid = null
    ): string;
}
