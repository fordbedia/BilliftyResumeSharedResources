<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Jobs;

use BilliftyResumeSDK\SharedResources\Modules\User\Mail\PasswordChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public ?string $name = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PasswordChangedMail($this->name));
    }
}
