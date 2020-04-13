<?php

namespace Omatech\Enigma\Database\Eloquent;

use Omatech\Enigma\Database\Contracts\DBInterface;

abstract class DBAbstract implements DBInterface
{
    abstract public function __construct(string $table);

    abstract public function insertHashes(int $modelId, string $column, array $hashes): void;

    abstract public function deleteHashes(int $modelId, string $column): void;

    abstract public function findByHash(string $column, string $hash): array;
}
