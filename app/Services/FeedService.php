<?php

namespace App\Services;

use App\Http\Resources\PostCollection;
use App\Repositories\PostRepository;
use App\Repositories\UserPostViewRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeedService
{
    private const CACHE_TTL = 300;
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;

    public function __construct(
        private PostRepository $postRepository,
        private UserPostViewRepository $userPostViewRepository
    ) {}

    /**
     * Get feed for user if user is logged in
     *
     * @param string|null $userId
     * @param int $limit
     * @param int $offset
     * @param string $sortBy
     * @param string $sortDirection
     * @return PostCollection
     */
    public function getFeedForUser(
        ?string $userId = null,
        int $limit = self::DEFAULT_LIMIT,
        int $offset = 0,
        string $sortBy = 'hotness',
        string $sortDirection = 'desc'
    ): PostCollection {
        $limit = min($limit, self::MAX_LIMIT);
        $sortBy = in_array($sortBy, ['hotness', 'created_at']) ? $sortBy : 'hotness';
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $cacheKey = $this->getFeedCacheKey($userId, $limit, $offset, $sortBy, $sortDirection);

        if (!$userId) {
            $cachedFeed = Cache::get($cacheKey);
            if ($cachedFeed) {
                Log::info('Feed served from cache', ['cache_key' => $cacheKey]);
                return $cachedFeed;
            }
        }

        $posts = $this->postRepository->getPostsForFeed(
            $userId,
            $limit,
            $offset,
            $sortBy,
            $sortDirection
        );

        if (!$userId) {
            Cache::put($cacheKey, $posts, self::CACHE_TTL);
        }

        Log::info('Feed generated', [
            'user_id' => $userId,
            'posts_count' => $posts->count(),
            'total_available' => $this->postRepository->getAvailablePostsCount($userId)
        ]);

        return $posts;
    }

    /**
     * Marks a post as viewed
     *
     * @param string $userId
     * @param int $postId
     * @return array
     */
    public function markPostAsViewed(string $userId, int $postId): array
    {
        try {
            if (!$this->postRepository->exists($postId)) {
                return [
                    'success' => false,
                    'message' => 'Post not found'
                ];
            }

            if ($this->userPostViewRepository->hasUserViewedPost($userId, $postId)) {
                return [
                    'success' => true,
                    'message' => 'Post already viewed',
                    'is_new_view' => false
                ];
            }

            $view = $this->userPostViewRepository->createOrUpdateView($userId, $postId);

            $this->postRepository->incrementViewCount($postId);

            $this->clearFeedCache();

            Log::info('Post marked as viewed', [
                'user_id' => $userId,
                'post_id' => $postId,
                'viewed_at' => $view->viewed_at
            ]);

            return [
                'success' => true,
                'message' => 'Post marked as viewed',
                'is_new_view' => true,
                'viewed_at' => $view->viewed_at
            ];

        } catch (\Exception $e) {
            Log::error('Error marking post as viewed', [
                'user_id' => $userId,
                'post_id' => $postId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Internal server error'
            ];
        }
    }

    /**
     * Get user stats
     *
     * @param string $userId
     * @return array
     */
    public function getUserStats(string $userId): array
    {
        $viewedPostIds = $this->userPostViewRepository->getViewedPostIds($userId);
        $totalAvailable = $this->postRepository->getAvailablePostsCount($userId);

        return [
            'viewed_posts_count' => count($viewedPostIds),
            'available_posts_count' => $totalAvailable,
            'completion_percentage' => $totalAvailable > 0
                ? round((count($viewedPostIds) / ($totalAvailable + count($viewedPostIds))) * 100, 2)
                : 0
        ];
    }

    private function getFeedCacheKey(
        ?string $userId,
        int $limit,
        int $offset,
        string $sortBy,
        string $sortDirection
    ): string {
        $userPart = $userId ? "user_{$userId}" : 'anonymous';
        return "feed_{$userPart}_{$limit}_{$offset}_{$sortBy}_{$sortDirection}";
    }

    private function clearFeedCache(): void
    {
        Cache::flush();
    }
}
