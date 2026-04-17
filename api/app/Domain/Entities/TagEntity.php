<?php

namespace App\Domain\Entities;

use InvalidArgumentException;
use JsonSerializable;

class TagEntity implements JsonSerializable
{
    private string $uuid;
    private string $name;

    public function __construct(string $uuid, string $name)
    {
        $this->uuid = $uuid;
        $this->setName($name);
    }

    public static function fromArray(array $data): self
    {
        return new self($data['uuid'], $data['name']);
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
            throw new InvalidArgumentException('Tag name cannot be empty.');
        }

        $this->name = $name;
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
        ];
    }
}
