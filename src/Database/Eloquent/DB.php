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
     * @param array $hashIds
     */
    public function setModelId(int $modelId, array $hashIds): void
    {
        if (! count($hashIds)) {
            return;
        }

        $hashIds = implode(',', $hashIds);

        Query::statement("UPDATE $this->table SET model_id = '$modelId' WHERE id IN ($hashIds)");
    }

    /**
     * @param string $name
     * @param string $hash
     * @return int
     */
    public function insertHash(string $name, string $hash): int
    {
        Query::statement("INSERT INTO $this->table (name, hash) values ('$name', '$hash')");

        return Query::getPdo()->lastInsertId();
    }

    /**
     * @param int $modelId
     * @param string $column
     * @return void
     */
    public function deleteHash(int $modelId, string $column): void
    {
        Query::statement("DELETE FROM $this->table WHERE model_id = $modelId AND name = $column");
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
