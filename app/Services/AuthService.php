<?php

namespace App\Services;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;


class AuthService
{




    public function authenticateAzure(string $idToken, string $accessToken): array|null
    {
        try {

            if (!$this->validateToken($idToken) || !$this->validateToken($accessToken)) {
                return null;
            }

            if (!$this->verifyJwtSignature($idToken)) {
                Log::error('Invalid JWT signature for the ID token.');
                return null;
            }

            return $this->getUserDetails($idToken);
        } catch (\Exception $e) {
            Log::error('Error in AuthService: authenticateAzure function: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Verify the JWT signature using Microsoft's public key.
     *
     * @param string $jwt
     * @return bool
     */
    public function verifyJwtSignature(string $jwt): bool
    {
        try {

            // Step 1: Validate the JWT.
            if (!$this->decodeJwtHeader($jwt)) {
                return false;
            }

            // Step 2: Extract the Key ID from the JWT header.
            $kid = (new Parser(new JoseEncoder()))->parse($jwt)
                ->headers()
                ->get('kid') ?? null;


            if (!$kid) {
                Log::error('JWT does not contain a valid kid.');
                return false;
            }

            // Step 3: Retrieve the Microsoft JWKS
            $jwks = $this->getMicrosoftPublicKeys();

            // Step 4: Find the matching key using the kid from the token.
            $key = $this->getKeyFromJWKS($kid, $jwks);

            if (!$key) {
                Log::error('No matching public key found for kid');
                return false;
            }

            // Step 5: Extract the public key from the JWKS.
            $publicKey = $this->getPublicKeyFromJWKS($key);
            if (!$publicKey) {
                Log::error('Public key not found in JWKS.');
                return false;
            }

            // Step 6:verify the JWT using the public key.
            JWT::decode($jwt, new Key($publicKey, 'RS256'));
            return true;
        } catch (\Exception $e) {
            Log::error('Error in AuthService: verifyJwtSignature function: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Get the public key from the JWKS data.
     *
     * @param array $key
     * @return string
     */
    private function getPublicKeyFromJWKS(array $key): string|bool
    {
        if (isset($key['x5c'][0])) {
            return "-----BEGIN CERTIFICATE-----\n" .
                chunk_split($key['x5c'][0], 64, "\n") .
                "-----END CERTIFICATE-----\n";
        }
        return false;
    }

    /**
     * Decode the JWT header to extract the Key ID (kid).
     *
     * @param string $jwt
     * @return array
     */
    private function decodeJwtHeader(string $jwt): array|bool
    {

        $tokenParts = explode('.', $jwt);

        if (count($tokenParts) !== 3) {
            Log::error('Invalid JWT format');
            return false;
        }
        return true;
    }

    /**
     * Retrieve the JWKS (JSON Web Key Set) from Microsoft's URL
     *
     * @return array
     */
    private function getMicrosoftPublicKeys(): array
    {
        try {
            $response = Http::withoutVerifying()->get(config('auth.azure.microsoft_url'));
            if ($response->successful()) {
                $data = $response->json();
                return $data['keys'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Error in AuthService: getMicrosoftPublicKeys function: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Get the key from the JWKS using the kid from the token.
     *
     * @param string $kid
     * @param array $jwks
     * @return array|null
     */
    private function getKeyFromJWKS(string $kid, array $jwks): ?array
    {
        foreach ($jwks as $key) {
            if ($key['kid'] === $kid) {
                return $key;
            }
        }
        return null;
    }

    private function getUserDetails(string $idToken): array|null
    {
        try {

            $claims = (new Parser(new JoseEncoder()))->parse($idToken)->claims();
            $personalId = explode('@', $claims->get('preferred_username'))[0];
            $name = $claims->get('name');

            return [
                'personal_id' => $personalId,
                'name' => $name,
            ];
        } catch (\Exception $e) {
            Log::error('Error in AuthService: getUserDetails function: ' . $e->getMessage());
        }

        return null;
    }

    private function validateToken(string $token): bool
    {

        $token = (new Parser(new JoseEncoder()))->parse($token);

        if (!$token->hasBeenIssuedBy($this->getRequiredIssuer())) {
            return false;
        }

        if (!$token->isPermittedFor($this->getRequiredAudience())) {
            return false;
        }

        if ($token->isExpired(Carbon::now())) {
            return false;
        }

        if (!$token->isMinimumTimeBefore(Carbon::now())) {
            return false;
        }

        return true;
    }

    private function getRequiredIssuer(): string
    {
        $tenantId = config('auth.azure.tenant_id');
        return "https://login.microsoftonline.com/$tenantId/v2.0";
    }

    private function getRequiredAudience(): string
    {
        return config('auth.azure.audience');
    }
}
