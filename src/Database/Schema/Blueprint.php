<?php

namespace Omatech\Enigma\Database\Schema;

use Illuminate\Support\Facades\Schema;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * Create indexes table for main table.
     */
    public function enigma(): void
    {
        $indexTable = $this->getTable().'_index';

        if (! Schema::hasTable($indexTable)) {
            Schema::create($indexTable, static function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('model_id')->nullable();
                $table->string('name', 64);
                $table->string('hash', 704);

                $table->index(['name', 'hash']);
            });
        }
    }
}
