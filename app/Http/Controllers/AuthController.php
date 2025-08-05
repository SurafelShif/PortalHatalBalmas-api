<?php

namespace App\Http\Controllers;


use App\Enums\HttpStatusEnum;
use App\Services\LogService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Enums\HttpStatusCodeEnum;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class AuthController extends Controller
{
    protected $_authService;
    protected $_logService;
    public function __construct()
    {
        $this->_authService = new AuthService();
        $this->_logService = new LogService();
    }



    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user and revoke token",
     *     description="Logs out the authenticated user and revokes the access token.",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User is not authenticated",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */

    public function logout()
    {
        try {

            $user = Auth::user();

            $user->token()->revoke();

            $user->tokens()->delete();

            $cookieName = config('auth.token_name');

            $cookie = Cookie::forget($cookieName);

            return response()->json([
                'message' => 'התנתקת בהצלחה.'
            ], Response::HTTP_OK)->withCookie($cookie);
        } catch (\Exception $e) {
            Log::error('Error in AuthController: logout function: ' . $e->getMessage());
            $this->_logService->logToS3();
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    // --------------------------------- Azure ----------------------------------
    public function loginAzure(LoginRequest $request)
    {


        try {

            $idToken = $request->input('idToken');
            $result = $this->_authService->authenticateAzure($idToken);
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

            //PasswordGrantClient
            $client = Client::where('password_client', true)->first();
            if (is_null($client)) {
                return response()->json('', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $tokenResponse = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $user->personal_id,
                'password' => $idToken, // Passport will call validateForPassportPasswordGrant().
                'scope' => '',
            ]);
            if ($tokenResponse->failed()) {
                Log::error('Error from AuthController: loginAzure function: ' . $tokenResponse->body(),);
                $this->_logService->logToS3();
                return response()->json([
                    'message' => 'קרתה בעיית התחברות. יש לפנות למסגרת אמת.',
                ], $tokenResponse->status());
            }

            return response()->json([
                'user' => [
                    'name' => $user->full_name,
                    'personal_id' => $user->personal_id,
                ]
            ], Response::HTTP_OK)->withCookie(
                Cookie::make('access_token', $tokenResponse->json('access_token'), config('auth.token_lifetime.access_token'))
            )->withCookie(
                Cookie::make('refresh_token', $tokenResponse->json('refresh_token'), config('auth.token_lifetime.refresh_token'))
            );
        } catch (\Exception $e) {
            Log::error('Error from AuthController: loginAzure function: ' . $e->getMessage());
            $this->_logService->logToS3();
            return $e->getMessage() === "Expired token" ?
                response()->json("", Response::HTTP_CONFLICT) :
                response()->json("", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function refreshToken(Request $request): mixed
    {

        try {

            $rules = [
                'refresh_token' => 'required|string',
            ];

            $customMessages = [
                'refresh_token.refresh_token' => 'בקשה אינה תקינה',
                'refresh_token.string' => 'בקשה אינה תקינה',
            ];

            $validator = Validator::make($request->all(), $rules, $customMessages);

            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $client = Client::where('password_client', true)->first();

            if (is_null($client)) {
                return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->refresh_token,
                'client_id' => $client->id,
                'client_secret' => $client->secret,
            ]);

            if ($response->failed()) {
                return response()->json(['message' => 'Invalid refresh token or other issue.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()
                ->json(['message' => 'משתמש מחובר.'], Response::HTTP_OK)
                ->withCookie(
                    Cookie::make('access_token', $response->json('access_token'), config('auth.token_lifetime.access_token'))
                )
                ->withCookie(
                    Cookie::make('refresh_token', $response->json('refresh_token'), config('auth.token_lifetime.refresh_token'))
                );
        } catch (\Exception $e) {
            Log::error('Error from AuthController: refreshToken function: ' . $e->getMessage());
            return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function isValidPersonalId($personalID)
    {
        return (bool)preg_match('/^\d{9}$/', $personalID);
    }
}
