<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
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
}
