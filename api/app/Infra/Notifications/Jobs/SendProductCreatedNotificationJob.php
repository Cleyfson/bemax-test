<?php

namespace App\Infra\Notifications\Jobs;

use App\Infra\Notifications\Mail\ProductCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendProductCreatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly ProductCreatedMail $mail,
    ) {}

    public function handle(): void
    {
        Mail::to(config('mail.notification_email'))->send($this->mail);
    }
}
