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
