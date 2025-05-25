<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Services\GlobalService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    public function __construct(private ImageService $imageService) {}
    public function save(Request $request)
    {
        $result = $this->imageService->uploadImage($request->image);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json([
            'data' => $result,

        ], Response::HTTP_CREATED);
    }
}
