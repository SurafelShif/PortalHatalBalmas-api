<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreateInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Services\InformationsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InformationController extends Controller
{
    public function __construct(private InformationsService $InformationsService) {}
    /**
     * @OA\Get(
     *     path="/api/info",
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
     *     path="/api/info",
     *     summary="יוצר כתבת מידע חדש",
     *     description="יוצר כתבת מידע חדש עם כותרת, תוכן ותמונה",
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
     *     type="string",
     *     example="{blah:{dfgfdgfd}"
     * ),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="הכתבת מידע נוצר בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="נתונים חסרים או שגויים",
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
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/info/{uuid}",
     *     summary="מוחק כתבת מידע לפי UUID",
     *     description="מוחק כתבת מידע בודד לפי מזהה ייחודי (UUID).",
     *     operationId="deleteInformationByUUid",
     *     tags={"Informations"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הכתבת מידע",
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
     *         description="הכתבת מידע לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function deleteInformation($uuid)
    {
        $result = $this->InformationsService->deleteInformation($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::INFORMATION_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/info/{uuid}",
     *     summary="מעדכן פוסט ",
     *     description="מעדכן פוסט .",
     *     operationId="updateInformation",
     *     tags={"Informations"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של כתבת מידע",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", example="כותרת כתבת מידע"),
     *                 @OA\Property(property="description", type="string", example="תיאור קצר של כתבת מידע"),
     *  @OA\Property(
     *     property="content",
     *     type="string",
     *     example="{blah:{dfgfdgfd}"
     * ),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת לפוסט")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="כתבת מידע נוצר בהצלחה",
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
    public function updateInformation($uuid, UpdateInformationRequest $request)
    {
        $result = $this->InformationsService->updateInformation($uuid, $request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::INFORMATION_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'data' => $result,
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/info/{uuid}",
     *     summary="מביא פוסט לפי UUID",
     *     description="מביא פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="getInformationByUUid",
     *     tags={"Informations"},
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
    public function getInformationByUUid($uuid)
    {
        $result = $this->InformationsService->getInformationByUUid($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::INFORMATION_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
}
