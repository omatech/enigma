<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Database\Schema\Blueprint;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
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
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder(): \Illuminate\Database\Schema\MySqlBuilder
    {
        $builder = parent::getSchemaBuilder();
        // Laravel 12+ resolves blueprints with ($connection, $table, $callback); earlier versions with
        // ($table, $callback). Passing every argument through keeps Schema::table()'s callback on both.
        $builder->blueprintResolver(static fn (...$arguments) => new Blueprint(...$arguments));

        return $builder;
    }
}
