<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Schema\Blueprint;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function getSchemaBuilder(): \Illuminate\Database\Schema\MySqlBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
