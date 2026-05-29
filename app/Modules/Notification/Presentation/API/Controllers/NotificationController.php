<?php

namespace App\Modules\Notification\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Application\UseCases\GetUserNotificationsUseCase;
use App\Modules\Notification\Application\UseCases\MarkNotificationsReadUseCase;
use App\Modules\Notification\Domain\Contracts\NotificationRepositoryInterface;
use App\Modules\Notification\Presentation\API\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
    ) {}

    /**
     * GET /api/v1/notifications
     */
    public function index(Request $request, GetUserNotificationsUseCase $useCase): JsonResponse
    {
        $paginator = $useCase->execute($request->user()->id);

        return response()->json(
            NotificationResource::collection($paginator)->response()->getData(true)
        );
    }

    /**
     * POST /api/v1/notifications/mark-read
     * Body: { ids?: int[] }  — omit ids to mark all as read.
     */
    public function markRead(Request $request, MarkNotificationsReadUseCase $useCase): JsonResponse
    {
        $ids = $request->input('ids', []);

        $useCase->execute($request->user()->id, $ids);

        $unread = $this->notificationRepo->unreadCount($request->user()->id);

        return response()->json(['message' => 'Marked as read.', 'unread_count' => $unread]);
    }

    /**
     * DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->notificationRepo->delete($id, $request->user()->id);

        if (! $deleted) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        return response()->json(['message' => 'Notification deleted.']);
    }

    /**
     * POST /api/v1/notifications/{id}/mark-read
     */
    public function markSingleRead(Request $request, int $id, MarkNotificationsReadUseCase $useCase): JsonResponse
    {
        $useCase->execute($request->user()->id, [$id]);

        $unread = $this->notificationRepo->unreadCount($request->user()->id);

        return response()->json(['message' => 'Marked as read.', 'unread_count' => $unread]);
    }

    /**
     * GET /api/v1/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $this->notificationRepo->unreadCount($request->user()->id),
        ]);
    }
}
