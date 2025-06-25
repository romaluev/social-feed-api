<?php

namespace App\Repositories;

use App\Models\UserPostView;
use Illuminate\Support\Collection;

class UserPostViewRepository
{
    public function __construct(
        private UserPostView $model
    ) {}

    /**
     * Create or update user post view
     *
     * @param string $userId
     * @param int $postId
     * @return UserPostView
     */
    public function createOrUpdateView(string $userId, int $postId): UserPostView
    {
        return $this->model->firstOrCreate(
            [
                'user_id' => $userId,
                'post_id' => $postId,
            ],
            [
                'viewed_at' => now(),
            ]
        );
    }

    /**
     * Check if user has viewed the post
     *
     * @param string $userId
     * @param int $postId
     */
    public function hasUserViewedPost(string $userId, int $postId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }

    /**
     * Get all post ids viewed by user
     *
     * @param string $userId
     */
    public function getViewedPostIds(string $userId): array
    {
        return $this->model
            ->where('user_id', $userId)
            ->pluck('post_id')
            ->toArray();
    }

    /**
     * Get post view count
     *
     * @param int $postId
     */
    public function getPostViewCount(int $postId): int
    {
        return $this->model
            ->where('post_id', $postId)
            ->count();
    }

    /**
     * Get recent views by user
     *
     * @param string $userId
     * @param int $limit
     */
    public function getRecentViewsByUser(string $userId, int $limit = 50): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('viewed_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
