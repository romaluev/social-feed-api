<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeedRequest;
use App\Http\Resources\PostCollection;
use App\Models\Post;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedController extends Controller
{
    public function __construct(
        private readonly FeedService $feedService
    ) {
//        $this->authorizeResource(Post::class, 'post');
    }

    /**
     * Get public feed (without user filtering)
     *
     * @param FeedRequest $request
     * @return JsonResponse
     */
    public function index(FeedRequest $request): JsonResponse
    {
        $feed = $this->feedService->getFeedForUser(
            userId: null,
            limit: $request->validated('limit', 20),
            offset: $request->validated('offset', 0),
            sortBy: $request->validated('sort_by', 'hotness'),
            sortDirection: $request->validated('sort_direction', 'desc')
        );

        return response()->json(
            $feed,
            Response::HTTP_OK
        );
    }

    /**
     * Get personalized feed for authenticated user
     *
     * @param FeedRequest $request
     * @return JsonResponse
     */
    public function userFeed(FeedRequest $request, int $userId): JsonResponse
    {
//        $this->authorize('viewAny');

        $feed = $this->feedService->getFeedForUser(
            userId: $userId,
            limit: $request->validated('limit', 20),
            offset: $request->validated('offset', 0),
            sortBy: $request->validated('sort_by', 'hotness'),
            sortDirection: $request->validated('sort_direction', 'desc')
        );

        return response()->json(
            $feed,
            Response::HTTP_OK
        );
    }

    /**
     * Get user statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userStats(Request $request, int $userId): JsonResponse
    {
//        $this->authorize('viewAny');

        $stats = $this->feedService->getUserStats($userId);

        return response()->json([
            'success' => true,
            'data' => $stats
        ], Response::HTTP_OK);
    }
}
