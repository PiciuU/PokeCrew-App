<?php

namespace Framework\Support\Facades;

class Validator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'validator';
    }
}