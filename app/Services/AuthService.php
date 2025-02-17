<?php

namespace App\Services;

use Alancting\Microsoft\JWT\AzureAd\AzureAdAccessTokenJWT;
use Alancting\Microsoft\JWT\AzureAd\AzureAdConfiguration;
use Alancting\Microsoft\JWT\AzureAd\AzureAdIdTokenJWT;
use App\Enums\HttpStatusEnum;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{

    public function login($request)
    {
        try {
            if (!isset($request->personal_id)  || preg_match('/^\d{9}$/', $request->personal_id) !== 1) {
                return HttpStatusEnum::BAD_REQUEST;
            }
            $user = User::where('personal_id', $request->personal_id)->first();
            if (is_null($user)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            $tokenName = config('auth.access_token_name');
            $token = $user->createToken($tokenName);
            return ["token" => $token, "tokenName" => $tokenName, "user" => $user];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function authenticateAzure(string $idToken, string $accessToken)
    {
        $config_options = [
            'tenant' => config('auth.azure.tenant'),
            'tenant_id' => config('auth.azure.tenant_id'),
            'client_id' => config('auth.azure.client_id')
        ];
        $config = new AzureAdConfiguration($config_options);
        $audience = config('auth.azure.audience');
        try {
            /**
             * If id token is invalid, exception will be thrown.
             * You could also pass $audience if needed
             */
            $idTokenJWT = new AzureAdIdTokenJWT($config, $idToken, $audience);
            /**
             * If id token is invalid, exception will be thrown.
             * To validate and decode access token jwt, you need to pass $audience (scope name of your app)
             */
            $accessTokenJWT = new AzureAdAccessTokenJWT($config, $accessToken, $audience);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return abort(Response::HTTP_FORBIDDEN);
        }
        /**
         * To check whether the access token and id token are expired, simply call
         */
        abort_if($idTokenJWT->isExpired() || $accessTokenJWT->isExpired(), Response::HTTP_FORBIDDEN);
        $personalId = strtok($idTokenJWT->getPayload()->preferred_username, '@');
        $name = $idTokenJWT->getPayload()->name;
        return (object) [
            'personalId' => $personalId,
            'name' => $name
        ];
    }
}
