<?php

namespace Sawirricardo\LaravelFilepond\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sawirricardo\LaravelFilepond\Filepond
 */
class Filepond extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Sawirricardo\LaravelFilepond\Filepond::class;
    }
}
