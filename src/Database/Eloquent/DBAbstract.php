<?php

namespace Omatech\Enigma\Database\Eloquent;

use Omatech\Enigma\Database\Contracts\DBInterface;

abstract class DBAbstract implements DBInterface
{
    abstract public function __construct(string $table);

    abstract public function setModelId(int $modelId, array $hashIds): void;

    abstract public function insertHash(string $name, string $hash): int;

    abstract public function deleteHash(int $modelId, string $column): void;

    abstract public function findByHash(string $column, string $hash): array;
}
