<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostViewRequest;
use App\Models\Post;
use App\Services\FeedService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class PostViewController extends Controller
{
    public function __construct(
        private readonly FeedService $feedService
    ) {
//        $this->authorizeResource(Post::class, 'post');
    }
    /**
     * Mark post as viewed by user
     *
     * @param PostViewRequest $request
     * @param int $userId
     * @param int $post
     * @return JsonResponse
     */
    public function store(PostViewRequest $request, int $userId, int $post): JsonResponse
    {
//        $this->authorize('view', $post);

        $result = $this->feedService->markPostAsViewed($userId, $post);

        $statusCode = $result['success'] ? Response::HTTP_OK :
            ($result['message'] === 'Post not found' ? Response::HTTP_NOT_FOUND : Response::HTTP_INTERNAL_SERVER_ERROR);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => array_key_exists('is_new_view', $result) ? [
                'is_new_view' => $result['is_new_view'],
                'viewed_at' => $result['viewed_at'] ?? null
            ] : null
        ], $statusCode);
    }
}
