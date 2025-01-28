<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function __construct(private AuthService $AuthService) {}

    /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="login",
     *      tags={"Authentication"},
     *      summary="Login user",
     *      description="Authenticate and login user based on personal id",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"personal_id"},
     *              @OA\Property(property="personal_id", type="string", example="123456789")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="הפעולה התבצעה בהצלחה",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="בקשה לא תקינה",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="משתמש אינו נמצא",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="אירעה שגיאה",
     *      ),
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $result = $this->AuthService->login($request);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::INVALID => response()->json(ResponseMessages::INVALID_REQUEST, Response::HTTP_BAD_REQUEST),
                HttpStatusEnum::BAD_REQUEST => response()->json(ResponseMessages::INVALID_REQUEST, Response::HTTP_BAD_REQUEST),
                HttpStatusEnum::NOT_FOUND => response()->json(ResponseMessages::USER_NOT_FOUND, Response::HTTP_NOT_FOUND),
                HttpStatusEnum::ERROR => response()->json(ResponseMessages::ERROR_OCCURRED, Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        $token = $result['token'];
        $tokenName = $result['tokenName'];
        $user = $result['user'];
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
            'user' => new UserResource($user),
        ])->withCookie(Cookie::make($tokenName, $token->accessToken));
    }
    /**
     * @OA\Get(
     *      path="/api/user",
     *      operationId="user",
     *      tags={"Users"},
     *      summary="Get authenticated user",
     *      description="Returns the authenticated user's details",
     *      @OA\Response(
     *          response=200,
     *          description="הפעולה התבצעה בהצלחה",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="המשתמש לא מחובר",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="אירעה שגיאה",
     *      )
     * )
     */
    public function user()
    {
        $result = $this->AuthService->getLoggedUser();
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
}
