<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Schema\Blueprint;

class SQLiteConnection extends \Illuminate\Database\SQLiteConnection
{
    public function getSchemaBuilder(): \Illuminate\Database\Schema\SQLiteBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
