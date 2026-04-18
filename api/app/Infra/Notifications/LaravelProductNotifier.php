<?php

namespace App\Infra\Notifications;

use App\Application\Contracts\ProductNotifierInterface;
use App\Domain\Entities\ProductEntity;
use App\Infra\Notifications\Jobs\SendProductCreatedNotificationJob;
use App\Infra\Notifications\Jobs\SendProductDeletedNotificationJob;
use App\Infra\Notifications\Mail\ProductCreatedMail;
use App\Infra\Notifications\Mail\ProductDeletedMail;

class LaravelProductNotifier implements ProductNotifierInterface
{
    public function notifyCreated(ProductEntity $product): void
    {
        SendProductCreatedNotificationJob::dispatch(
            ProductCreatedMail::fromEntity($product)
        );
    }

    public function notifyDeleted(ProductEntity $product): void
    {
        SendProductDeletedNotificationJob::dispatch(
            ProductDeletedMail::fromEntity($product)
        );
    }
}
