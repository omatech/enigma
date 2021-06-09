<?php

namespace Omatech\Enigma\Database\Query;

use Omatech\Enigma\CipherSweet\Index;
use Omatech\Enigma\Enigma;

class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * @param string $column
     * @param string $value
     * @param Index $index
     * @param string $boolean
     * @return $this
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     */
    public function whereEnigma(string $column, string $value, Index $index, string $boolean = 'and'): self
    {
        $this->findByHash($column, $value, $index, $boolean);

        return $this;
    }

    /**
     * @param string $column
     * @param string $value
     * @param Index $index
     * @return $this
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     */
    public function orWhereEnigma(string $column, string $value, Index $index): self
    {
        return $this->whereEnigma($column, $value, $index, 'or');
    }

    /**
     * @param string $column
     * @param string $value
     * @param Index $index
     * @param string $boolean
     * @return Builder
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     */
    private function findByHash(string $column, string $value, Index $index, string $boolean): self
    {
        $table = $this->from;
        $tableColumn = explode('.', $column);

        if (count($tableColumn) === 2) {
            [$table, $column] = $tableColumn;
        }

        $parts = explode(' as ', $table);
        $table = $parts[0];
        $alias = $parts[1] ?? null;

        $ids = (new Enigma)->search($table, $column, $value, $index);

        $closure = static function (self $query) use ($table, $alias, $ids) {
            $query->whereRaw(($alias ?? $table) . ".id IN ($ids)");
        };

        $boolean === 'and'
        ? $this->where($closure)
        : $this->orWhere($closure);

        return $this;
    }
}
