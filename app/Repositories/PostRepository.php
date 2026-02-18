<?php
namespace App\Repositories;

use App\Models\Post;

class PostRepository
{
    public function getPaginated($perPage = 10)
    {
        return Post::latest()->paginate($perPage);
    }

    public function findByExternalId($externalId)
    {
        return Post::where('external_id', $externalId)->first();
    }

    public function saveMany(array $posts)
    {
        foreach ($posts as $post) {

            Post::updateOrCreate(
                ['external_id' => $post['id']],
                [
                    'title' => $post['title'],
                    'body' => $post['body']
                ]
            );

        }
    }

    public function count()
    {
        return Post::count();
    }
}
