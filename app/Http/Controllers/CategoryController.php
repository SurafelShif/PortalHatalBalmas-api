<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="מביא את כל הקטגוריות",
     *     description="מביא את כל הקטגוריות עם מספר הפוסטים בכל קטגוריה",
     *     operationId="getCategories",
     *     tags={"Categories"},
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
    public function getCategories()
    {
        $result = $this->categoryService->getCategories();
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{categoryId}",
     *     summary="מוחק קטגוריה",
     *     description="מוחק קטגוריה על פי מזהה ID",
     *     operationId="deleteCategory",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="המזהה של הקטגוריה",
     *         @OA\Schema(type="integer")
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
    public function deleteCategory($categoryId)
    {
        $result = $this->categoryService->deleteCategory($categoryId);
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
     *     path="/api/categories/{categoryId}",
     *     summary="מעודכן קטגוריה",
     *     description="מעודכן שם קטגוריה",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="categoryId",
     *         in="path",
     *         required=true,
     *         description="המזהה של הקטגוריה",
     *         @OA\Schema(type="integer")
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
    public function updateCategory(CategoryRequest $request, $categoryId)
    {
        $result = $this->categoryService->updateCategory($categoryId, $request->name);
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
            'message' => ResponseMessages::SUCCESS_ACTION
        ], Response::HTTP_OK);
    }
}
