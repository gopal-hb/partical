<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ExternalPostService;
use App\Repositories\PostRepository;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    //

  
    protected $service;
    protected $repository;

    public function __construct(
        ExternalPostService $service,
        PostRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index()
    {
        if ($this->repository->count() == 0) {

            $this->service->syncPosts();
        }

        $posts = $this->repository->getPaginated(10);

        return PostResource::collection($posts);
    }

    public function show($id)
    {
        $post = $this->repository->findByExternalId($id);

        if (!$post) {

            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        return new PostResource($post);
    }
}


