<?php

namespace Framework\Services\Auth\Token;

use Framework\Database\ORM\Model;

/**
 * Class PersonalAccessToken
 *
 * The PersonalAccessToken class represents a personal access token in the authentication system.
 * It extends the base ORM Model and provides methods for finding tokens and retrieving related tokenable entities.
 *
 * @package Framework\Services\Auth\Token
 */
class PersonalAccessToken extends Model
{
    /**
     * The fillable attributes for the model.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'tokenable_type',
        'tokenable_id',
        'last_used_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The hidden attributes for the model.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Find a personal access token by its hashed value.
     *
     * @param string $token The hashed token value.
     * @return PersonalAccessToken|null The found PersonalAccessToken instance or null if not found.
     */
    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }
    }

    /**
     * Retrieve the entity related to the token.
     *
     * @return \Framework\Database\ORM\Model|null The related entity instance or null if not found.
     */
    public function relatedTo()
    {
        return class_exists($this->tokenable_type) ? $this->tokenable_type::find($this->tokenable_id) : null;
    }
}