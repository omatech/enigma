<?php

namespace Omatech\Enigma\Database\Eloquent;

use Illuminate\Support\Facades\DB as Query;

final class DB extends DBAbstract
{
    private $table;

    /**
     * DB constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table.'_index';
    }

    /**
     * @param int $modelId
     * @param string $column
     * @param array $hashes
     * @return void
     */
    public function insertHashes(int $modelId, string $column, array $hashes): void
    {
        foreach (array_chunk($hashes, 100) as $chunks) {
            $insert = array_map(function ($hash) use ($modelId, $column) {
                return "('$modelId', '$column', '$hash')";
            }, $chunks);

            Query::statement("INSERT INTO $this->table (model_id, name, hash) VALUES ".implode(',', $insert));
        }
    }

    /**
     * @param int $modelId
     * @param string $column
     * @return void
     */
    public function deleteHashes(int $modelId, string $column): void
    {
        Query::statement("DELETE FROM $this->table WHERE model_id = $modelId AND name = '$column'");
    }

    /**
     * @param string $column
     * @param string $hash
     * @return array
     */
    public function findByHash(string $column, string $hash): array
    {
        $query = Query::select(
            "SELECT model_id FROM $this->table
            WHERE name = '$column' AND
                  hash = '$hash' AND
                  model_id IS NOT NULL"
        );

        return array_map(static function ($row) {
            return $row->model_id;
        }, $query ?? []);
    }
}
