<?php

namespace Framework\Support\Facades;

class Request extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}