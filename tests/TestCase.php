<?php

namespace Sawirricardo\LaravelFilepond\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Sawirricardo\LaravelFilepond\FilepondServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            FilepondServiceProvider::class,
        ];
    }
}
