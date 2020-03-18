<?php

use Omatech\Enigma\Enigma;

if (! function_exists('encryptEnigma')) {
    function encryptEnigma(string $tableName, string $column, string $value): string
    {
        return (new Enigma())->encrypt($tableName, $column, $value);
    }
}

if (! function_exists('decryptEnigma')) {
    function decryptEnigma(string $tableName, string $column, string $value): string
    {
        return (new Enigma())->decrypt($tableName, $column, $value);
    }
}
