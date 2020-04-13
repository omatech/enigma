<?php

namespace Omatech\Enigma\Database\Contracts;

interface DBInterface
{
    public function __construct(string $table);

    public function insertHashes(int $modelId, string $column, array $hashes): void;

    public function deleteHashes(int $modelId, string $column): void;

    public function findByHash(string $column, string $hash): array;
}
