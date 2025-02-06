<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateSiteRequest;
use App\Services\SitesService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     *
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
    public function getSites()
    {
        $result = $this->sitesService->getSites();
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
        ], Response::HTTP_OK);
    }
}
