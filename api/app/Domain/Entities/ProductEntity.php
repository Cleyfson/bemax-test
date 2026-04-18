<?php

namespace App\Domain\Entities;

use InvalidArgumentException;
use JsonSerializable;

class ProductEntity implements JsonSerializable
{
    private string $uuid;
    private string $name;
    private string $slug;
    private float $price;
    private ?string $description;
    private CategoryEntity $category;
    /** @var TagEntity[] */
    private array $tags;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $uuid,
        string $name,
        string $slug,
        float $price,
        ?string $description,
        CategoryEntity $category,
        array $tags = [],
        ?string $createdAt = null,
        ?string $updatedAt = null,
    ) {
        $this->uuid = $uuid;
        $this->setName($name);
        $this->slug = $slug;
        $this->setPrice($price);
        $this->description = $description;
        $this->category = $category;
        $this->tags = $tags;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        $category = $data['category'] instanceof CategoryEntity
            ? $data['category']
            : CategoryEntity::fromArray($data['category']);

        $tags = array_map(
            fn($tag) => $tag instanceof TagEntity ? $tag : TagEntity::fromArray($tag),
            $data['tags'] ?? []
        );

        return new self(
            $data['uuid'],
            $data['name'],
            $data['slug'],
            (float) $data['price'],
            $data['description'] ?? null,
            $category,
            $tags,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null,
        );
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Product name must not exceed 255 characters.');
        }

        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Product price cannot be negative.');
        }

        $this->price = $price;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCategory(): CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): void
    {
        $this->category = $category;
    }

    /** @return TagEntity[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    /** @param TagEntity[] $tags */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid'        => $this->uuid,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'price'       => $this->price,
            'description' => $this->description,
            'category'    => $this->category->jsonSerialize(),
            'tags'        => array_map(fn(TagEntity $t) => $t->jsonSerialize(), $this->tags),
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
