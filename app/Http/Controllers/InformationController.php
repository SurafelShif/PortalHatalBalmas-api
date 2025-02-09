<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateInformationRequest;
use App\Services\InformationsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends Controller
{
    public function __construct(private InformationsService $InformationsService) {}
    /**
     * @OA\Get(
     *     path="/api/informations",
     *     summary="מקבל את רשימת הכתבות מידע",
     *     description="מחזיר רשימה של כל הכתבות מידע במערכת.",
     *     operationId="getInformations",
     *     tags={"Informations"},
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
    public function getInformations()
    {
        $result = $this->InformationsService->getInformations();
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/informations",
     *     summary="יוצר כתבת מידע חדש",
     *     description="יוצר כתבת מידע חדש עם כותרת, תיאור, תוכן, קטגוריה ותמונה.",
     *     operationId="createInformation",
     *     tags={"Informations"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "content", "image"},
     *                 @OA\Property(property="title", type="string", example="כותרת הכתבת מידע"),
     *  @OA\Property(
     *     property="content",
     *     type="object",
     *     example={
     *         "title": "Sample Post",
     *         "body": "This is the full content of the post",
     *         "author": "John Doe"
     *     }
     * ),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת לכתבת מידע")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הכתבת מידע נוצר בהצלחה",
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
    public function createInformation(CreateInformationRequest $request)
    {
        $result = $this->InformationsService->createInformation($request->title, $request->content, $request->image);
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
