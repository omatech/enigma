<?php

namespace Omatech\Enigma;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Omatech\Enigma\Commands\IndexHydrateCommand;
use Omatech\Enigma\Database\Contracts\DBInterface;
use Omatech\Enigma\Database\Eloquent\DB;
use Omatech\Enigma\Database\MySqlConnection;
use Omatech\Enigma\Database\PostgresConnection;
use Omatech\Enigma\Database\SQLiteConnection;
use Omatech\Enigma\Database\SqlServerConnection;

class EnigmaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('enigma.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        app()->singleton(DBInterface::class, DB::class);

        Connection::resolverFor('mysql', static function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('pgsql', static function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('sqlite', static function ($connection, $database, $prefix, $config) {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('sqlsrv', static function ($connection, $database, $prefix, $config) {
            return new SqlServerConnection($connection, $database, $prefix, $config);
        });

        $this->commands(IndexHydrateCommand::class);

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'enigma');
    }
}
