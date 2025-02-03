<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\AddAdminRequest;
use App\Services\UsersService;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(private UsersService $usersService) {}
    /**
     * @OA\Post(
     *      path="/api/users/admins",
     *      operationId="store",
     *      tags={"Users"},
     *      summary="add admins",
     *      description="add admins",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="string",
     *                  format="uuid",
     *                  example="b143c4ab-91a7-481a-ab1a-cf4a00d2fc11"
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="הפעולה התבצעה בהצלחה",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="משתמש אינו נמצא",
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="משתמש זה הינו מנהל מערכת",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="אירעה שגיאה",
     *      )
     * )
     */
    public function addAdmin(AddAdminRequest $request)
    {
        $result = $this->usersService->addAdmin($request->fullName, $request->personal_id);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::CONFLICT => response()->json(["message" => ResponseMessages::ALREADY_ADMIN], Response::HTTP_CONFLICT),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION
        ], Response::HTTP_CREATED);
    }
}
