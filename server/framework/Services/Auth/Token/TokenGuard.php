<?php

namespace Framework\Services\Auth\Token;

use Framework\Http\Request;

use Carbon\Carbon;

/**
 * Class TokenGuard
 *
 * The TokenGuard class is responsible for handling authentication using personal access tokens.
 * It validates the token from the request, checks its validity, and associates it with the corresponding tokenable entity.
 *
 * @package Framework\Services\Auth\Token
 */
class TokenGuard
{
    /**
     * Handle the token authentication logic.
     *
     * @param Request $request The HTTP request object.
     * @return mixed|null The tokenable entity associated with the valid access token or null if authentication fails.
     */
    public function __invoke(Request $request)
    {
        if ($token = $this->getTokenFromRequest($request)) {
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$this->isValidAccessToken($accessToken) || !$this->supportsTokens($tokenable = $accessToken->relatedTo())) {
                return;
            }

            $accessToken->update([
                'last_used_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            return $tokenable->withAccessToken($accessToken);
        }

    }

    /**
     * Extract the token from the request.
     *
     * @param \Framework\Http\Request $request The HTTP request object.
     * @return string|null The extracted token or null if not found or invalid.
     */
    protected function getTokenFromRequest(Request $request)
    {
        $token = $request->bearerToken();

        return $this->isValidBearerToken($token) ? $token : null;
    }

    /**
     * Check if the bearer token is valid.
     *
     * @param string|null $token The bearer token to validate.
     * @return bool True if the bearer token is valid; otherwise, false.
     */
    protected function isValidBearerToken(string $token = null)
    {
        if (!is_null($token) && str_contains($token, '|')) {
            $model = new PersonalAccessToken();

            [$id, $token] = explode('|', $token, 2);

            return ctype_digit($id) && !empty($token);
        }

        return !empty($token);
    }

    /**
     * Check if the access token is valid.
     *
     * @param PersonalAccessToken|null $accessToken The access token to validate.
     * @return bool True if the access token is valid; otherwise, false.
     */
    protected function isValidAccessToken($accessToken)
    {
        if (!$accessToken) {
            return false;
        }

        $isValid = !$accessToken->expires_at || $accessToken->expires_at > date('Y-m-d H:i:s');

        return $isValid;
    }

    /**
     * Check if the tokenable entity supports tokens.
     *
     * @param mixed $tokenable The tokenable entity.
     * @return bool True if the tokenable entity supports tokens; otherwise, false.
     */
    protected function supportsTokens($tokenable)
    {
        return $tokenable && in_array(Tokenable::class, class_uses(get_class($tokenable)));
    }
}