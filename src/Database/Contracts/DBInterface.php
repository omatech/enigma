<?php

namespace Omatech\Enigma\Database\Contracts;

interface DBInterface
{
    public function __construct(string $table);

    public function setModelId(int $modelId, array $hashIds): void;

    public function insertHash(string $name, string $hash): int;

    public function deleteHash(int $modelId, string $column): void;

    public function findByHash(string $column, string $hash): array;
}
