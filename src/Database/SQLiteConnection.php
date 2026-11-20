<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Database\Schema\Blueprint;

class SQLiteConnection extends \Illuminate\Database\SQLiteConnection
{
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\SQLiteBuilder
     */
    public function getSchemaBuilder(): \Illuminate\Database\Schema\SQLiteBuilder
    {
        $builder = parent::getSchemaBuilder();
        // Laravel 12+ resolves blueprints with ($connection, $table, $callback); earlier versions with
        // ($table, $callback). Passing every argument through keeps Schema::table()'s callback on both.
        $builder->blueprintResolver(static fn (...$arguments) => new Blueprint(...$arguments));

        return $builder;
    }
}
