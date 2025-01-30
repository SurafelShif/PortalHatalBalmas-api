<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Services\PostsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PostsController extends Controller
{
    public function __construct(private PostsService $postsService) {}

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="מביא רשימת חדשות",
     *     description="מביא רשימת חדשות עם אפשרות לסינון לפי קטגוריה, חיפוש ודפדוף.",
     *     operationId="getPosts",
     *     tags={"Posts"},
     *
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="סינון לפי קטגוריה",
     *         required=false,
     *         @OA\Schema(type="string", example="ספורט")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="מילת חיפוש בכותרת, תיאור או תוכן",
     *         required=false,
     *         @OA\Schema(type="string", example="טכנולוגיה")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="כמות פריטים בעמוד",
     *         required=false,
     *         @OA\Schema(type="integer", default=3)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="מספר עמוד לתצוגה",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
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
    public function getPosts(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $perPage = $request->query('per_page', 3);
        $page = $request->query('page', 1);
        $result = $this->postsService->getPosts($category, $perPage, $page, $search);
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
     * @OA\Get(
     *     path="/api/posts/{uuid}",
     *     summary="מביא פוסט לפי UUID",
     *     description="מביא פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="getPostByUUid",
     *     tags={"Posts"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט",
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
     *         description="הפוסט לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function getPostByUUid($uuid)
    {
        $result = $this->postsService->getPostByUUid($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
            'data' => $result,
        ], Response::HTTP_OK);
    }
}
