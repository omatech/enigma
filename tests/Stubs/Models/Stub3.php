<?php

namespace Omatech\Enigma\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Omatech\Enigma\Database\Eloquent\HasEnigma;

class Stub3 extends Model
{
    use HasEnigma;

    protected $table = 'stub3';

    protected $enigmaEncryptable = [
        'name',
    ];
    protected $laravelEncryptable = [
        'name',
    ];
}
