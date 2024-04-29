<?php

namespace Framework\Support\Facades;

class Storage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}