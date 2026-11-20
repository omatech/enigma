<?php

namespace Omatech\Enigma\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel 12+ calls the blueprint resolver with ($connection, $table, $callback). A resolver that only accepted
 * ($table, $callback) lost the callback of Schema::table(), so every column change ran as an empty ALTER.
 */
class SchemaTableTest extends TestCase
{
    public function test_schema_table_applies_its_callback_on_the_enigma_connection(): void
    {
        Schema::table('stub1', static function (Blueprint $table) {
            $table->string('added_by_alter')->nullable();
        });

        $this->assertTrue(Schema::hasColumn('stub1', 'added_by_alter'));
    }

    public function test_enigma_blueprint_still_creates_the_index_table_from_an_alter(): void
    {
        Schema::create('stub4', static function (Blueprint $table) {
            $table->bigIncrements('id');
        });
        Schema::table('stub4', static function ($table) {
            $table->enigma();
        });

        $this->assertTrue(Schema::hasTable('stub4_index'));
    }
}
