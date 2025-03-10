<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Services\SitesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class SiteController extends Controller
{
    public function __construct(private SitesService $sitesService) {}
    /**
     * @OA\Get(
     *     path="/api/sites",
     *     summary="מקבל את רשימת הקישורים",
     *     description="מחזיר רשימה של כל הקישורים במערכת.",
     *     operationId="getSites",
     *     tags={"Sites"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="מילת חיפוש בכותרת, תיאור או תוכן",
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
    public function getSites(Request $request)
    {
        $query = $request->query("query");
        $result = $this->sitesService->getSites($query);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/sites",
     *     summary="יוצר קישור חדש",
     *     description="יוצר קישור חדש עם השם, התיאור, הקישור, התוכן והתמונה שסופקו.",
     *     operationId="createSite",
     *     tags={"Sites"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="קישור לדוגמה"),
     *                 @OA\Property(property="description", type="string", example="תיאור של הקישור"),
     *                 @OA\Property(property="link", type="string", format="url", example="https://example.com"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הקישור נוצר בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function createSite(CreateSiteRequest $request)
    {
        $result = $this->sitesService->createSite($request->name, $request->description, $request->link, $request->image);
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
     * @OA\Delete(
     *     path="/api/sites/{uuid}",
     *     summary="מוחק פוסט לפי UUID",
     *     description="מוחק פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="deleteSiteByUuid",
     *     tags={"Sites"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט למחיקה",
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
     *         description="הפוסט לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function deleteSite($uuid)
    {
        $result = $this->sitesService->deleteSite($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/sites/{uuid}",
     *     summary="עדכון פוסט לפי UUID",
     *     description="מעודכן פוסט קיים לפי מזהה ייחודי (UUID) עם הנתונים החדשים שסופקו.",
     *     operationId="updateSiteByUuid",
     *     tags={"Sites"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט לעדכון",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="נתוני הפוסט המעודכנים בפורמט טופס",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="כותרת חדשה"),
     *                 @OA\Property(property="description", type="string", example="תוכן מעודכן של הפוסט"),
     *                 @OA\Property(property="link", type="string", example="https://www.google.com/"),
     *                 @OA\Property(property="image", type="string", format="binary", description="קובץ תמונה לפוסט (לא חובה)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הפוסט לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */


    public function updateSite($uuid, UpdateSiteRequest $request)
    {
        $result = $this->sitesService->updateSite($uuid, $request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'data' => $result,
        ], Response::HTTP_OK);
    }
}
