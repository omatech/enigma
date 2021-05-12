<?php

namespace Omatech\Enigma\Tests;

include_once 'src/Helpers/enigma.php';

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Omatech\Enigma\EnigmaServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/Stubs/Migrations');
        $this->artisan('migrate');
    }

    protected function getPackageProviders($app)
    {
        return [EnigmaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'mysql');

        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'host'     => env('DB_MYSQL_HOST'),
            'database' => env('DB_MYSQL_DATABASE'),
            'username' => env('DB_MYSQL_USER'),
            'password' => env('DB_MYSQL_PASSWORD'),
        ]);

        $app['config']->set('database.connections.pgsql', [
            'driver'   => 'pgsql',
            'host'     => env('DB_PGSQL_HOST'),
            'database' => env('DB_PGSQL_DATABASE'),
            'username' => env('DB_PGSQL_USER'),
            'password' => env('DB_PGSQL_PASSWORD'),
        ]);

        $app['config']->set('database.connections.sqlsrv', [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_SQLSRV_HOST'),
            'database' => env('DB_SQLSRV_DATABASE'),
            'username' => env('DB_SQLSRV_USER'),
            'password' => env('DB_SQLSRV_PASSWORD'),
        ]);

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/Stubs/database.sqlite',
        ]);
    }
}
