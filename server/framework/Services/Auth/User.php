<?php

namespace Framework\Services\Auth;

use Framework\Database\ORM\Model;

use Framework\Services\Auth\Traits\Authenticatable;

/**
 * Class User
 *
 * The User class represents a user entity in the authentication system.
 * It extends the base ORM Model and uses the Authenticatable trait to add authentication-related functionality.
 *
 * @package Framework\Services\Auth
 */
class User extends Model
{
    use Authenticatable;
}
