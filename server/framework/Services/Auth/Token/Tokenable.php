<?php

namespace Framework\Services\Auth\Token;

use Framework\Support\Str;

use Carbon\Carbon;

/**
 * Trait Tokenable
 *
 * The Tokenable trait provides methods for working with personal access tokens.
 * It allows a model to create, retrieve, and manage personal access tokens associated with it.
 *
 * @package Framework\Services\Auth\Token
 */
trait Tokenable
{
    /**
     * The current access token associated with the model.
     *
     * @var PersonalAccessToken
     */
    protected $accessToken;

    /**
     * Create a new personal access token for the model.
     *
     * @param string $name The name of the token (default is 'user').
     * @param Carbon|null $expiresAt The expiration date and time for the token (null for no expiration).
     * @return NewAccessToken The new access token instance.
     */
    public function createToken(string $name = 'user', $expiresAt = null)
    {
        $plainTextToken = sprintf(
            '%s%s%s',
            '',
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );

        $token = PersonalAccessToken::create([
            'name' => $name,
            'tokenable_type' => static::class,
            'tokenable_id' => $this->{$this->primaryKey},
            'token' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Retrieve all personal access tokens associated with the model.
     *
     * @return \Framework\Support\Collections\Collection The collection of personal access tokens.
     */
    public function tokens()
    {
        return PersonalAccessToken::where('tokenable_id', $this->id)->get();
    }

    /**
     * Get the current access token associated with the model.
     *
     * @return PersonalAccessToken|null The current access token or null if none is set.
     */
    public function currentAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token associated with the model.
     *
     * @param PersonalAccessToken $accessToken The access token to set.
     * @return $this
     */
    public function withAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}