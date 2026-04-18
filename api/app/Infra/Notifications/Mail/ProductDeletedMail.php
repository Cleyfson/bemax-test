<?php

namespace App\Infra\Notifications\Mail;

use App\Domain\Entities\ProductEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly float  $price,
        public readonly string $deletedAt,
    ) {}

    public static function fromEntity(ProductEntity $product): self
    {
        return new self(
            uuid:      $product->getUuid(),
            name:      $product->getName(),
            price:     $product->getPrice(),
            deletedAt: now()->toIso8601String(),
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Product deleted: {$this->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product-deleted',
        );
    }
}
