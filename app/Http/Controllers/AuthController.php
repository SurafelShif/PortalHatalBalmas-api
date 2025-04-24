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
    public function __construct(private AuthService $authService) {}
    public function loginAzure(LoginRequest $request)
    {
        $idToken = $request->idToken;
        $accessToken = $request->accessToken;
        try {
            $result = $this->authService->authenticateAzure($idToken, $accessToken);
            if (is_null($result)) {
                return response()->json(['message' => 'אחד או יותר מהנתונים שנשלחו אינו תקין'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $personalID = $result['personal_id'];
            if (!$this->isValidPersonalId($personalID)) {
                return response()->json(['message' => 'פרטי myidf שגויים'], Response::HTTP_FORBIDDEN);
            }
            $user = User::where(column: 'personal_id', operator: $personalID)->first();
            if (is_null($user)) {
                return response()->json(['message' => 'המשתמש לא קיים במערכת, יש לפנות למסגרת אמ"ת.'], Response::HTTP_NOT_FOUND);
            }
            // Revoke old token
            $user->tokens()->delete();
            $tokenName = config('auth.token_name');
            // Create new token with expiration date
            $token = $user->createToken($tokenName);
            return response()
                ->json(
                    [
                        'name' => $user->full_name,
                        'personal_id' => $user->personal_id,
                    ],
                    Response::HTTP_OK,
                )
                ->withCookie(Cookie::make($tokenName, $token->accessToken));
        } catch (\Exception $e) {
            Log::error('Error from AuthController: loginAzure function: ' . $e->getMessage());
            $this->_logService->logToS3();
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
