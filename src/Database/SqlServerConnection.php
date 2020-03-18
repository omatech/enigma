<?php

namespace Omatech\Enigma\Database;

use Omatech\Enigma\Database\Schema\Blueprint;

class SqlServerConnection extends \Illuminate\Database\SqlServerConnection
{
    public function getSchemaBuilder(): \Illuminate\Database\Schema\SqlServerBuilder
    {
        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(static function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
