<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    /**
     * @OA\Get(
     *     path="/api/categories/{type}",
     *     summary="מביא את כל הקטגוריות",
     *     description="מביא את כל הקטגוריות עם מספר הפוסטים בכל קטגוריה",
     *     operationId="getCategories",
     *     tags={"Categories"},
     *      @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="קבלת קטגוריה לפי סוג",
     *         @OA\Schema(type="string")
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
    public function getCategories($type)
    {
        Log::error("sfdd");
        $result = $this->categoryService->getCategories($type);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{category_uuid}",
     *     summary="מוחק קטגוריה",
     *     description="מוחק קטגוריה על פי מזהה ID",
     *     operationId="deleteCategory",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="category_uuid",
     *         in="path",
     *         required=true,
     *         description="המזהה של הקטגוריה",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="קטגוריה נמחקה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הקטגוריה לא נמצאה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function deleteCategory($category_uuid)
    {
        $result = $this->categoryService->deleteCategory($category_uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::CATEGORY_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{category_uuid}",
     *     summary="מעודכן קטגוריה",
     *     description="מעודכן שם קטגוריה",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="category_uuid",
     *         in="path",
     *         required=true,
     *         description="המזהה של הקטגוריה",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="קטגוריה חדשה")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="קטגוריה עודכנה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הקטגוריה לא נמצאה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function updateCategory(CategoryRequest $request, $category_uuid)
    {
        $result = $this->categoryService->updateCategory($category_uuid, $request->name);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::CATEGORY_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'data' => $result
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="יוצר קטגוריה חדשה",
     *     description="יוצר קטגוריה חדשה במערכת",
     *     operationId="createCategory",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="קטגוריה חדשה")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="קטגוריה נוצרה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function createCategory(CategoryRequest $request)
    {
        $result = $this->categoryService->createCategory($request->name);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR)
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_CREATED);
    }
}
