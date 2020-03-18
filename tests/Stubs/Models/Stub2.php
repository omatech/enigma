<?php

namespace Omatech\Enigma\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Omatech\Enigma\Database\Eloquent\HasEnigma;

class Stub2 extends Model
{
    use HasEnigma;

    protected $table = 'stub2';
    protected $laravelEncryptable = [
        'name',
    ];
}
