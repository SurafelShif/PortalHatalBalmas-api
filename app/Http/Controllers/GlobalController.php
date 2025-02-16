<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Services\GlobalService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalController extends Controller
{
    public function __construct(private GlobalService $globalService) {}
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="מביא רשימת כל המידע לפי חיפוש",
     *     description="מביא רשימת כל המידע לפי חיפוש",
     *     operationId="getAllByQuery",
     *     tags={"GlobalSearch"},
     *

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
    public function search(Request $request)
    {

        $search = $request->query('query');
        $result = $this->globalService->search($search);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
}
