<?php

namespace Omatech\Enigma\Tests;

use Illuminate\Support\Facades\DB;
use Omatech\Enigma\CipherSweet\Index;
use Omatech\Enigma\Jobs\IndexHydrate;
use Illuminate\Support\Facades\Schema;
use Omatech\Enigma\Strategies\LikeSearch;
use Omatech\Enigma\Tests\Stubs\Models\Stub1;
use Omatech\Enigma\Tests\Stubs\Models\Stub2;
use Omatech\Enigma\Tests\Stubs\Models\Stub3;
use ParagonIE\CipherSweet\Transformation\Lowercase;
use Omatech\Enigma\Tests\Stubs\Strategies\EmailByDomain;
use Omatech\Enigma\Exceptions\RepeatedAttributesException;

class EncryptModelTestBase extends TestCase
{
    /** @test */
    public function check_for_enigma_indexes_tables(): void
    {
        $this->assertTrue(Schema::hasTable('stub1_index'));
        $this->assertTrue(Schema::hasTable('stub2_index'));
        $this->assertTrue(Schema::hasTable('stub3_index'));
    }

    /** @test */
    public function encrypt_with_enigma_on_model(): void
    {
        $this->expectsJobs(IndexHydrate::class);

        $stub = new Stub1();
        $stub->name = 'test';
        $stub->save();

        $this->assertSame($stub->name, 'test');

        $stub = Stub1::find($stub->id);

        $this->assertSame($stub->name, 'test');

        $this->assertDatabaseMissing('stub1', [
            'name' => 'test',
        ]);
    }

    /** @test */
    public function encrypt_with_laravel_on_model(): void
    {
        $stub = new Stub2();
        $stub->name = 'test';
        $stub->save();

        $this->assertSame($stub->name, 'test');

        $stub = Stub2::find($stub->id);

        $this->assertSame($stub->name, 'test');

        $this->assertDatabaseMissing('stub2', [
            'name' => 'test',
        ]);
    }

    /** @test */
    public function exception_on_encrypt_with_laravel_and_enigma_on_model(): void
    {
        $this->expectException(RepeatedAttributesException::class);

        $stub = new Stub3();
        $stub->name = 'test';
        $stub->save();
    }

    /** @test */
    public function find_for_where_encrypted_value(): void
    {
        $stub = new Stub1();
        $stub->name = 'test';
        $stub->save();

        $foundStub = Stub1::where('name', 'test')->first();

        $this->assertSame($stub->name, $foundStub->name);
        $this->assertSame($stub->id, $foundStub->id);
    }

    /** @test */
    public function find_for_or_where_encrypted_value(): void
    {
        $stub1 = new Stub1();
        $stub1->name = 'stub1';
        $stub1->save();

        $stub2 = new Stub1();
        $stub2->name = 'stub2';
        $stub2->save();

        $foundStub = Stub1::where('name', 'stub1')
            ->orWhere('name', 'stub2')
            ->get();

        $this->assertSame(count($foundStub), 2);
    }

    /** @test */
    public function find_no_results_on_where_encrypted_value(): void
    {
        $stub1 = new Stub1();
        $stub1->name = 'stub1';
        $stub1->save();

        $stub2 = new Stub1();
        $stub2->name = 'stub2';
        $stub2->save();

        $foundStub = Stub1::where('name', 'stub4')
            ->get();

        $this->assertSame(count($foundStub), 0);
    }

    /** @test */
    public function find_on_non_indexed_value(): void
    {
        $stub1 = new Stub1();
        $stub1->name = 'stub1';
        $stub1->birthday = '1989-03-08';
        $stub1->save();

        $foundStub = Stub1::where('birthday', '1989-03-08')
            ->get();

        $this->assertSame(count($foundStub), 0);
    }

    /** @test */
    public function update_encrypted_value(): void
    {
        $stub = new Stub1();
        $stub->name = 'test';
        $stub->save();

        $foundStub = Stub1::where('name', 'test')
            ->first();

        $foundStub->name = 'test2';
        $foundStub->save();

        $this->assertNotSame($stub->name, $foundStub->name);
    }

    /** @test */
    public function encrypt_multiple_fields_with_index(): void
    {
        $stub = new Stub1();
        $stub->name = 'test';
        $stub->surnames = 'test';
        $stub->save();

        $this->assertDatabaseHas('stub1_index', [
            'model_id' => $stub->id,
            'name' => 'name',
        ])->assertDatabaseHas('stub1_index', [
            'model_id' => $stub->id,
            'name' => 'surnames',
        ]);
    }

    /** @test */
    public function encrypt_without_index(): void
    {
        $stub = new Stub1();
        $stub->name = 'test';
        $stub->surnames = 'test';
        $stub->save();

        $this->assertDatabaseMissing('stub1_index', [
            'model_id' => $stub->id,
            'name' => 'birthday',
        ]);
    }

    /** @test */
    public function find_for_where_encrypted_value_with_query_builder(): void
    {
        $stub = new Stub1();
        $stub->name = 'test123';
        $stub->save();

        $index = (new Index)
            ->transformers([
                new Lowercase,
            ])
            ->strategies([
                new LikeSearch,
                new EmailByDomain,
            ])
            ->bits(256)
            ->fast();

        $foundStub = DB::table($stub->getTable())->whereEnigma('name', 'test123', $index)->first();

        $this->assertSame($stub->name, decryptEnigma($stub->getTable(), 'name', $foundStub->name));
        $this->assertSame($stub->id, (int) $foundStub->id);
    }

    /** @test */
    public function find_for_or_where_encrypted_value_with_query_builder(): void
    {
        $stub = new Stub1();
        $stub->name = 'test123';
        $stub->save();

        $stub = new Stub1();
        $stub->name = 'test321';
        $stub->save();

        $index = (new Index)
            ->transformers([
                new Lowercase,
            ])
            ->strategies([
                new LikeSearch,
                new EmailByDomain,
            ])
            ->bits(256)
            ->fast();

        $foundStub = DB::table($stub->getTable())
            ->whereEnigma('name', 'test654', $index)
            ->orWhereEnigma('name', 'test321', $index)
            ->first();

        $this->assertSame($stub->name, decryptEnigma($stub->getTable(), 'name', $foundStub->name));
        $this->assertSame($stub->id, (int) $foundStub->id);
    }

    /** @test */
    public function rehydrate_index_command(): void
    {
        $stub = new Stub1();
        $stub->name = 'test';
        $stub->surnames = 'test';
        $stub->save();

        $this->artisan('enigma:hydrate', ['namespace' => 'Omatech\Enigma\Tests\Stubs\Models'])
            ->expectsChoice('Which models would you like to hydrate?', "Omatech\Enigma\Tests\Stubs\Models\Stub1", [
                "All",
                "Omatech\Enigma\Tests\Stubs\Models\Stub1",
                "Omatech\Enigma\Tests\Stubs\Models\Stub2",
                "Omatech\Enigma\Tests\Stubs\Models\Stub3"
            ])
            ->expectsOutput('The index hydratation has been finished.')
            ->assertExitCode(0);
    }

    /** @test */
    public function rehydrate_all_index_command(): void
    {
        $this->expectException(RepeatedAttributesException::class);

        $stub = new Stub1();
        $stub->name = 'test';
        $stub->surnames = 'test';
        $stub->save();

        $this->artisan('enigma:hydrate', ['namespace' => 'Omatech\Enigma\Tests\Stubs\Models'])
            ->expectsChoice('Which models would you like to hydrate?', "All", [
                "All",
                "Omatech\Enigma\Tests\Stubs\Models\Stub1",
                "Omatech\Enigma\Tests\Stubs\Models\Stub2",
                "Omatech\Enigma\Tests\Stubs\Models\Stub3",
            ])
            ->assertExitCode(0);
    }
}
