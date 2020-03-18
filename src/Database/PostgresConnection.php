<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Schema\Blueprint;

class PostgresConnection extends \Illuminate\Database\PostgresConnection
{
    public function getSchemaBuilder(): \Illuminate\Database\Schema\PostgresBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
