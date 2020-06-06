<?php

namespace Oelmenshawy\UserIdGrant\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Oelmenshawy\UserIdGrant\Providers\UserIdServiceProvider;

abstract class AbstractTestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            UserIdServiceProvider::class,
        ];
    }
}
