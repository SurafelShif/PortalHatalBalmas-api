<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use Symfony\Component\HttpFoundation\Response;

class VerifyCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token');
        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_UNAUTHORIZED && $request->cookie('refresh_token')) {

            $client = Client::where('password_client', true)->first();
            if (!$client) {
                return response()->json(['message' => 'Client not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $refreshResponse = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->cookie('refresh_token'),
                'client_id' => $client->id,
                'client_secret' => $client->secret,
            ]);
            if ($refreshResponse->ok()) {
                // attach new token
                $request->headers->set('Authorization', 'Bearer ' . $refreshResponse->json('access_token'));
                // retry original req
                $response = $next($request);
                // attach new cookies to the outgoing response
                $response->headers->setCookie(
                    Cookie::make('access_token', $refreshResponse->json('access_token'), config('auth.token_lifetime.access_token'))
                );
                $response->headers->setCookie(
                    Cookie::make('refresh_token', $refreshResponse->json('refresh_token'), config('auth.token_lifetime.refresh_token'))
                );
                return $response;
            }
            return response()->json(['message' => 'יש להתחבר למערכת מחדש שוב.'], Response::HTTP_UNAUTHORIZED);
        }
        return $response;
    }
}
