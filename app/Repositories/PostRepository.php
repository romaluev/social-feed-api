<?php

namespace App\Repositories;

use App\Http\Resources\PostCollection;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostRepository
{
    public function __construct(
        private Post $model
    ) {}

    /**
     * Get posts for feed
     *
     * @param string|null $userId
     * @param int $limit
     * @param int $offset
     * @param string $sortBy
     * @param string $sortDirection
     * @return PostCollection
     */
    public function getPostsForFeed(
        ?string $userId = null,
        int $limit = 20,
        int $offset = 0,
        string $sortBy = 'hotness',
        string $sortDirection = 'desc'
    ): PostCollection {
        $query = $this->model->newQuery()
            ->select([
                'id', 'user_id', 'title', 'content',
                'hotness', 'view_count', 'created_at'
            ])
            ->with('user:id,name')
            ->where('view_count', '<', 1000);

        if ($userId) {
            $query->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select('id')
                    ->from('user_post_views')
                    ->whereColumn('user_post_views.post_id', 'posts.id')
                    ->where('user_post_views.user_id', $userId);
            });
        }

        return new PostCollection($query
            ->orderBy($sortBy, $sortDirection)
            ->offset($offset)
            ->paginate($limit));
    }


    /**
     * Finds a post by id.
     *
     * @param int $id Post ID
     * @return Post|null
     */
    public function findById(int $id): ?Post
    {
        return $this->model->find($id);
    }

    /**
     * Check if a post exists in the database.
     *
     * @param int $id Post Id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Increment the view count of a post.
     *
     * @param int $postId Post ID
     * @return bool
     */
    public function incrementViewCount(int $postId): bool
    {
        return $this->model
                ->where('id', $postId)
                ->increment('view_count') > 0;
    }

    /**
     * Get the number of available posts.
     *
     * @param string|null $userId
     * @return int
     */
    public function getAvailablePostsCount(?string $userId = null): int
    {
        $query = $this->model->newQuery()
            ->where('view_count', '<', 1000);

        if ($userId) {
            $query->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select('id')
                    ->from('user_post_views')
                    ->whereColumn('user_post_views.post_id', 'posts.id')
                    ->where('user_post_views.user_id', $userId);
            });
        }

        return $query->count();
    }
}
