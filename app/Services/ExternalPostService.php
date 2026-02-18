<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PostRepository;

class ExternalPostService
{
    protected $repository;

    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }

    public function syncPosts()
    {
        try {

            $response = Http::timeout(10)
                ->get('https://jsonplaceholder.typicode.com/posts');

            if ($response->successful()) {

                $posts = $response->json();

                $this->repository->saveMany($posts);

                Cache::put('posts_last_sync', now(), 3600);

                return true;
            }

            return false;

        } catch (\Exception $e) {

            return false;
        }
    }
}
