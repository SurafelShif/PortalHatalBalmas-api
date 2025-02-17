<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;

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
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::USER_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        $token = $result['token'];
        $tokenName = $result['tokenName'];
        $user = $result['user'];
        return response()->json(
            new UserResource($user),
        )->withCookie(Cookie::make($tokenName, $token->accessToken));
    }

    public function loginAzure(LoginRequest $request)
    {
        $idToken = $request->idToken;
        $accessToken = $request->accessToken;
        try {
            $result = $this->AuthService->authenticateAzure($idToken, $accessToken);
            $personalID = $result->personalId;
            if (!$this->isValidPersonalId($personalID)) {
                return response()->json('נא להתחבר עם תעודת זהות.', Response::HTTP_FORBIDDEN);
            }
            $name = $result->name;
            $user = User::where('personal_id', $personalID)->first();
            if (!$user) {
                return response()->json('משתמש לא נמצא במערכת. נא לפנות למנהל מערכת', Response::HTTP_NOT_FOUND);
            }
            $tokenName = config('auth.token_name');
            Token::where('name', $tokenName)
                ->where('user_id', $user->id)
                ->update(['revoked' => true]);
            if (empty($user->name)) {
                User::find($user->id)?->update(["name" => $name]);
            }
            return $user->createToken($tokenName)->accessToken;
        } catch (\Exception $e) {
            Log::error('Error from AuthController: loginAzure function: ' . $e->getMessage());
            return $e->getMessage() === "Expired token" ?
                response()->json("", Response::HTTP_CONFLICT) :
                response()->json("", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function isValidPersonalId($personalID)
    {
        return (bool)preg_match('/^\d{9}$/', $personalID);
    }
}
