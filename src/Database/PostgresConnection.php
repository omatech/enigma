<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Database\Schema\Blueprint;

class PostgresConnection extends \Illuminate\Database\PostgresConnection
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
     * @return \Illuminate\Database\Schema\PostgresBuilder
     */
    public function getSchemaBuilder(): \Illuminate\Database\Schema\PostgresBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
