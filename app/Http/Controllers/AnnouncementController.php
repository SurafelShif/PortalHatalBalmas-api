<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementPositionRequest;
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
     * @OA\Get(
     *     path="/api/announcements/admin",
     *     summary="מביא רשימת הכרזות",
     *     description="מביא רשימת הכרזות.",
     *     operationId="getAdminAnnouncements",
     *     tags={"Announcements"},
     *       @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="חיפוש הכרזות",
     *         required=false,
     *         @OA\Schema(type="string", example="")
     *     ),
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
    public function getAdminAnnouncements(Request $request)
    {
        $search = $request->query("query");
        $result = $this->announcementsService->getAdminAnnouncements($search);
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
     *     description="יוצר הכרזה חדש עם כותרת, תיאור, תוכן,  ותמונה.",
     *     operationId="createAnnouncements",
     *     tags={"Announcements"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "content", "image"},
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
        $result = $this->announcementsService->createAnnouncement($request->title, $request->description, $request->content, $request->image);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_CREATED);
    }
    /**
     * @OA\Post(
     *     path="/api/announcements/{uuid}",
     *     summary="מעדכן הכזרזות ",
     *     description="מעדכן הכרזות .",
     *     operationId="updateAnnouncement",
     *     tags={"Announcements"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="מערך של הכרזות עם שינויים",
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
    public function updateAnnouncement(string $uuid, UpdateAnnouncementRequest $request)
    {
        $result = $this->announcementsService->updateAnnouncement($uuid, $request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'data' => $result,
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
                HttpStatusEnum::NO_CONTENT => response()->json(["message" => ResponseMessages::NO_CONTENT], Response::HTTP_NO_CONTENT),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *     path="/api/announcements/{uuid}",
     *     summary="מביא פוסט לפי UUID",
     *     description="מביא פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="getAnnouncementByUUid",
     *     tags={"Announcements"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הכרזה",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="בקשה לא תקינה",
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
    public function getAnnouncementByUUid($uuid)
    {
        $result = $this->announcementsService->getAnnouncementByUUid($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Put(
     *     path="/api/announcements/updatePosition",
     *     summary="עדכון מיקומים של מספר הכרזות",
     *     description="מעודכן את המיקומים של מספר הכרזות בהתבסס על הנתונים שנשלחו.",
     *     operationId="updateAnnouncementPosition",
     *     tags={"Announcements"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="uuid", type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420"),
     *                 @OA\Property(property="position", type="integer", example=2)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="המיקומים עודכנו בהצלחה",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"message": "הפעולה בוצעה בהצלחה"}
     *         )
     *     ),
     *     @OA\Response(response=400, description="בקשה לא תקינה"),
     *     @OA\Response(response=404, description="אחת או יותר מההכרזות לא נמצאה"),
     *     @OA\Response(response=500, description="שגיאת שרת פנימית")
     * )
     */

    public function updateAnnouncementPosition(UpdateAnnouncementPositionRequest $request)
    {
        $result = $this->announcementsService->updateAnnouncementPosition($request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::ANNOUNCEMENT_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
}
