<?php

namespace Omatech\Enigma\Database\Eloquent;

use Closure;
use Omatech\Enigma\Database\Contracts\DBInterface;
use Omatech\Enigma\Enigma;

/**
 * @mixin \Illuminate\Database\Query\Builder
 * @method whereRaw($sql, $bindings = [], $boolean = 'and')
 */
class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Add a basic where clause to the query.
     *
     * @param Closure|string|array $column
     * @param mixed $operator
     * @param mixed $value
     * @param string $boolean
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): \Illuminate\Database\Eloquent\Builder
    {
        $enigma = (array) $this->model->getEnigmaEncryptable();

        if (! $column instanceof Closure &&
            in_array($column, $enigma, true) !== false) {
            [$value, $operator] = $this->query->prepareValueAndOperator(
                $value,
                $operator,
                func_num_args() === 2
            );

            if ($value) {
                return $this->findByHash($column, $value);
            }
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param \Closure|array|string $column
     * @param mixed $operator
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function orWhere($column, $operator = null, $value = null): \Illuminate\Database\Eloquent\Builder
    {
        $enigma = (array) $this->model->getEnigmaEncryptable();

        if (! $column instanceof Closure &&
            in_array($column, $enigma, true) !== false) {
            [$value, $operator] = $this->query->prepareValueAndOperator(
                $value,
                $operator,
                func_num_args() === 2
            );

            if ($value) {
                return $this->findByHash($column, $value, 'or');
            }
        }

        return parent::orWhere($column, $operator, $value);
    }

    /**
     * @param $column
     * @param $value
     * @param string $boolean
     * @return Builder
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    private function findByHash($column, $value, $boolean = 'and'): self
    {
        $hash = (new Enigma)
            ->getHash($this->getModel(), $column, $value, false);

        if ($hash !== null) {
            $ids = (app()->makeWith(DBInterface::class, [
                'table' => $this->getModel()->getTable(),
            ]))->findByHash($column, $hash);

            $ids = (count($ids) == 0) ? 'FALSE' : implode(',', $ids);

            $closure = static function (self $query) use ($ids) {
                $query->whereRaw("id IN ($ids)");
            };

            $boolean === 'and'
            ? $this->where($closure)
            : $this->orWhere($closure);
        }

        return $this;
    }
}
