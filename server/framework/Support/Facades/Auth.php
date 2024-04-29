<?php

namespace Framework\Support\Facades;

class Auth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'auth';
    }
}