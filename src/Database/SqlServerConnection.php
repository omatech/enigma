<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Database\Schema\Blueprint;

class SqlServerConnection extends \Illuminate\Database\SqlServerConnection
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
     * @return \Illuminate\Database\Schema\SqlServerBuilder
     */
    public function getSchemaBuilder(): \Illuminate\Database\Schema\SqlServerBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
