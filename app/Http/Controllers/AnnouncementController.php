<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementVisibility;
use App\Services\AnnouncementsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

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
        return response()->json(
            $result,
            Response::HTTP_OK
        );
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
    public function createAnnouncement(CreateAnnouncementRequest $request)
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
    /**
     * @OA\Post(
     *     path="/api/announcements/{uuid}",
     *     summary="מעדכן הכרזה ",
     *     description="מעדכן הכרזה .",
     *     operationId="updateAnnouncement",
     *     tags={"Announcements"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של ההכרזה",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="457946b0-6c14-4102-bf4a-675c61b228d1")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", example="כותרת ההכרזה"),
     *                 @OA\Property(property="description", type="string", example="תיאור קצר של ההכרזה"),
     *                 @OA\Property(property="content", type="object", example={"title": "Sample Post", "body": "This is the full content"}),
     *                 @OA\Property(property="position", type="integer", example=1),
     *                       @OA\Property(property="isVisible", type="integer", example=1,description="בוליאני מקבל רק 0 או 1"),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת להכרזה")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="הכרזה עודכנה בהצלחה",
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
    public function updateAnnouncement(UpdateAnnouncementRequest $request)
    {
        $result = $this->announcementsService->updateAnnouncement($request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Delete(
     *     path="/api/announcements/{uuid}",
     *     summary="מוחק הכרזה לפי UUID",
     *     description="מוחק הכרזה בודד לפי מזהה ייחודי (UUID).",
     *     operationId="deleteaAnnouncementsByUUid",
     *     tags={"Announcements"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של ההכרזה",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הכרזה לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function deleteAnnouncement($uuid)
    {
        $result = $this->announcementsService->deleteAnnouncement($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Patch(
     *     path="/api/announcements/{uuid}",
     *     summary="עדכון נראות של הכרזה",
     *     description="עדכון מאפיין ה- isVisible של הכרזה מסוימת לפי UUID.",
     *     operationId="updateAnnouncementVisibility",
     *     tags={"Announcements"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של ההכרזה",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="457946b0-6c14-4102-bf4a-675c61b228d1")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"isVisible"},
     *                 @OA\Property(property="isVisible", type="boolean", example=true, description="קובע האם ההכרזה גלויה או מוסתרת")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="נראות ההכרזה עודכנה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הכרזה לא נמצאה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function updateAnnouncementVisibility($uuid, UpdateAnnouncementVisibility $request)
    {
        $result = $this->announcementsService->updateAnnouncementVisibility($uuid, $request->isVisible);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
}
