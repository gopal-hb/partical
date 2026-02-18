<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ExternalPostService;

class SyncPostsJob implements ShouldQueue
{
    use Queueable;
    protected $service;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
         $this->service = app(ExternalPostService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $this->service->syncPosts();
    }
}
