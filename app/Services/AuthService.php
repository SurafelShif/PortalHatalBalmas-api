<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;


class AuthService
{


    protected $_logService;

    public function __construct()
    {
        $this->_logService = new LogService();
    }


    /**
     * Authenticate the user with Azure using the provided ID token.
     *
     * @param string $idToken
     * @return array|null
     */

    public function authenticateAzure(string $idToken): ?array
    {
        try {

            if (!$this->validateIdToken($idToken)) {
                return null;
            }
            return $this->getUserDetails($idToken);
        } catch (\Exception $e) {
            Log::error('Error in AuthService: authenticateAzure function: ' . $e->getMessage());
            $this->_logService->logToS3();
        }

        return null;
    }

    /**
     * Extract user details from the decoded ID token.
     * @param string $idToken
     * @return array|null
     */

    private function getUserDetails(string $idToken): ?array
    {

        try {

            $claims = (new Parser(new JoseEncoder()))->parse($idToken)->claims();

            $personalId = explode('@', $claims->get('preferred_username'))[0];
            $name = $claims->get('name');

            return [
                'personal_id' => $personalId,
                'name' => $name
            ];
        } catch (\Exception $e) {
            Log::error('Error in AuthService: getUserDetails function: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get the public key associated with the provided Key ID (kid).
     *
     * @param string $tokenKid
     * @return array|null
     */

    private function getPublicKey(string $tokenKid): ?array
    {
        $publicKeys = $this->getMicrosoftKeys();
        foreach ($publicKeys as $publicKey) {
            if ($publicKey['kid'] === $tokenKid) {
                return [
                    'kid' => $publicKey['kid'],
                    'x5c' => $publicKey['x5c']
                ];
            }
        }
        return null;
    }

    /**
     * Retrieve the JWKS from Microsoft's URL.
     *
     * @return array
     */
    private function getMicrosoftKeys(): array
    {
        try {

            $microsoftKeysUrl = $this->getMicrosoftKeysUrl();

            $client = new Client([
                'verify' => false
            ]);

            // $client = new Client();

            $keys = [];


            $res = $client->get($microsoftKeysUrl);

            $body = json_decode($res->getBody()->getContents(), true);
            if (!array_key_exists('keys', $body)) {
                return [];
            }

            $keys = $body['keys'];
            foreach ($keys as $key) {
                $keys[] = [
                    'kid' => $key['kid'],
                    'x5c' => $key['x5c'],

                ];
            }

            return $keys;
        } catch (\Exception $e) {
            Log::error('Error in AuthService: getMicrosoftKeys function: ' . $e->getMessage());
        }

        return $keys;
    }

    /**
     * Extract the Key ID from the JWT header.
     * @param string $idToken
     * @return string|null
     */

    private function extractKid(string $idToken): ?string
    {
        try {

            $token = (new Parser(new JoseEncoder()))->parse($idToken);

            if (!$token->headers()->has('kid')) {
                return null;
            }

            return $token->headers()->get('kid');
        } catch (\Exception $e) {
            Log::error('Error in AuthService: extractKid function: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Build the URL for Microsoft's JWKS endpoint based on the tenant ID.
     *
     * @return string
     */
    private function getMicrosoftKeysUrl(): string
    {
        $tenantId = config('auth.azure.tenant_id');
        return "https://login.microsoftonline.com/$tenantId/discovery/v2.0/keys";
    }

    /**
     * Get the required issuer for the token verification.
     *
     * @return string
     */
    private function getRequiredIssuer(): string
    {
        $tenantId = config('auth.azure.tenant_id');
        return "https://login.microsoftonline.com/$tenantId/v2.0";
    }

    /**
     * Get the required audience for the token verification.
     *
     * @return string The expected audience for the tokens.
     */

    private function getRequiredAudience(): string
    {
        return config('auth.azure.audience');
    }


    /**
     * Validate the ID token by checking its claims and verifying it against the public key.
     *
     * @param string $idToken
     * @return bool
     */

    private function validateIdToken(string $idToken): bool
    {
        try {

            $tokenKid = $this->extractKid($idToken);
            if (is_null($tokenKid)) {
                return false;
            }

            $publicKey = $this->getPublicKey($tokenKid);

            if (is_null($publicKey) || empty($publicKey['x5c'])) {
                return false;
            }

            $pemKey = InMemory::plainText($this->generatePem($publicKey['x5c'][0]));
            $signer = new Sha256();

            $config = Configuration::forAsymmetricSigner(
                signer: $signer,
                signingKey: $pemKey,
                verificationKey: $pemKey
            );

            $token = $config->parser()->parse($idToken);
            if (!$token->headers()->has('kid')) {
                return false;
            }

            $clock = new SystemClock(new \DateTimeZone('UTC'));
            // 60 seconds difference, in case we are out of sync
            $leeway = new \DateInterval('PT60S');

            $constraints = [
                new IssuedBy($this->getRequiredIssuer()), // Validate issuer.
                new PermittedFor($this->getRequiredAudience()), // Validate audience.
                new StrictValidAt($clock, $leeway), // Validate expiration.
                new SignedWith($signer, $pemKey) // Verify the token with the public key.
            ];

            return $config->validator()->validate($token, ...$constraints);
        } catch (\Exception $e) {
            Log::error('Error in AuthService: validateIdToken function: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Generate a PEM certificate from the x5c certificate string.
     *
     * @param string $x5c
     * @return string
     */

    private function generatePem(string $x5c): string
    {
        return "-----BEGIN CERTIFICATE-----\n" .
            wordwrap($x5c, 64, "\n", true) .
            "\n-----END CERTIFICATE-----";
    }
}
