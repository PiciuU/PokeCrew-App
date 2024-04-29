<?php

namespace Framework\Services\Auth\Token;

/**
 * Class NewAccessToken
 *
 * The NewAccessToken class represents a new access token along with its plain text version.
 * It is used to encapsulate and provide convenient methods for handling newly generated access tokens.
 *
 * @package Framework\Services\Auth\Token
 */
class NewAccessToken
{
    /**
     * The access token object.
     *
     * @var PersonalAccessToken
     */
    public $accessToken;

    /**
     * The plain text version of the access token.
     *
     * @var string
     */
    public $plainTextToken;

    /**
     * NewAccessToken constructor.
     *
     * @param PersonalAccessToken $accessToken The access token object.
     * @param string $plainTextToken The plain text version of the access token.
     */
    public function __construct(PersonalAccessToken $accessToken, string $plainTextToken)
    {
        $this->accessToken = $accessToken;
        $this->plainTextToken = $plainTextToken;
    }

    /**
     * Convert the NewAccessToken instance to an array.
     *
     * @return array The array representation of the NewAccessToken.
     */
    public function toArray()
    {
        return [
            'accessToken' => $this->accessToken,
            'plainTextToken' => $this->plainTextToken,
        ];
    }

    /**
     * Convert the NewAccessToken instance to JSON.
     *
     * @param int $options JSON encoding options.
     * @return string The JSON representation of the NewAccessToken.
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
