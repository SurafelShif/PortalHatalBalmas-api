<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Services\AnnouncementsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnnouncementController extends Controller
{

    public function __construct(private AnnouncementsService $announcementsService) {}
    /**
     * @OA\Get(
     *     path="/api/announcements",
     *     summary="מביא רשימת הכרזות",
     *     description="מביא רשימת הכרזות.",
     *     operationId="getAnnouncements",
     *     tags={"Announcements"},
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function getAnnouncements()
    {
        $result = $this->announcementsService->getAnnouncements();
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
            'data' => $result,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/announcements",
     *     summary="יוצר הכרזה חדשה",
     *     description="יוצר הכרזה חדש עם כותרת, תיאור, תוכן, מיקום ותמונה.",
     *     operationId="createAnnouncements",
     *     tags={"Announcements"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "content", "position", "image"},
     *                 @OA\Property(property="title", type="string", example="כותרת ההכרזה"),
     *                 @OA\Property(property="description", type="string", example="תיאור קצר של ההכרזה"),
     *  @OA\Property(
     *     property="content",
     *     type="object",
     *     example={
     *         "title": "Sample Post",
     *         "body": "This is the full content of the post",
     *         "author": "John Doe"
     *     }
     * ),
     *                 @OA\Property(property="position", type="integer", example=1),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת להכרזה")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="ההכרזה נוצר בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="בקשה לא תקינה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function createAnnouncement(Request $request)
    {
        $result = $this->announcementsService->createAnnouncement($request->title, $request->description, $request->content, $request->position, $request->image);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
}
