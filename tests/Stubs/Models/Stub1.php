<?php

namespace Omatech\Enigma\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Omatech\Enigma\CipherSweet\Index;
use Omatech\Enigma\Database\Eloquent\HasEnigma;
use Omatech\Enigma\Strategies\LikeSearch;
use Omatech\Enigma\Tests\Stubs\Strategies\EmailByDomain;
use ParagonIE\CipherSweet\Transformation\Lowercase;

class Stub1 extends Model
{
    use HasEnigma;

    protected $table = 'stub1';
    protected $enigmaEncryptable = [
        'name',
        'surnames',
        'birthday',
    ];

    public function nameBlindIndex(Index $index): void
    {
        $index
            ->transformers([
                new Lowercase,
            ])
            ->strategies([
                new LikeSearch,
                new EmailByDomain,
            ])
            ->bits(256)
            ->fast();
    }

    public function surnamesBlindIndex(Index $index): void
    {
        $index
            ->transformers([
                new Lowercase,
            ])
            ->bits(256)
            ->fast();
    }
}
