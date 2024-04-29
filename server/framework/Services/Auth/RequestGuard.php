<?php

namespace Framework\Services\Auth;

use Framework\Services\Auth\Token\TokenGuard;

/**
 * Class RequestGuard
 *
 * The RequestGuard class represents an authentication guard based on the request.
 * It uses the TokenGuard to authenticate the user based on the request.
 *
 * @package Framework\Services\Auth
 */
class RequestGuard
{
    use Traits\GuardHelpers;

   /**
     * The request instance.
     *
     * @var \Framework\Http\Request
     */
    protected $request;

    /**
     * RequestGuard constructor.
     *
     * Initializes the RequestGuard with the current request instance.
     */
    public function __construct()
    {
        $this->request = request();
    }

    /**
     * Get the authenticated user.
     *
     * If the user is not already set, it uses the TokenGuard to attempt to authenticate the user based on the request.
     *
     * @return mixed|null The authenticated user instance or null if authentication fails.
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $this->user = (new TokenGuard)($this->request);

        return $this->user;
    }

}