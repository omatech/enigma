<?php

namespace Omatech\Enigma\Database\Eloquent;

use Throwable;
use SodiumException;
use Omatech\Enigma\Enigma;
use Omatech\Enigma\Database\Contracts\DBInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Exceptions\RepeatedAttributesException;
use ParagonIE\CipherSweet\Exception\CryptoOperationException;
use Omatech\Enigma\Database\Eloquent\Builder as EloquentBuilder;
use ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException;

trait HasEnigma
{
    private $engine;
    public static $indexIds = [];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     * @return void
     * @throws Throwable
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->engine = new Enigma;

        $repeated = count(array_intersect($this->getLaravelEncryptable(), $this->getEnigmaEncryptable()));

        throw_if($repeated, new RepeatedAttributesException('One or more attributes are repeated on both encryptation types.'));
    }

    /**
     * @return array
     */
    public function getLaravelEncryptable(): array
    {
        return $this->laravelEncryptable ?? [];
    }

    /**
     * @return array
     */
    public function getEnigmaEncryptable(): array
    {
        return $this->enigmaEncryptable ?? [];
    }

    /**
     * Boot trait on the model.
     *
     * @return void
     */
    public static function bootHasEnigma(): void
    {
        static::saving(static function ($model) {
            $model->encrypt(
                $model->getDirty()
            );
        });

        static::retrieved(static function ($model) {
            $model->decrypt(
                $model->attributes
            );
        });

        static::saved(static function ($model) {
            (app()->makeWith(DBInterface::class, [
                'table' => $model->getTable(),
            ]))->setModelId($model->id, static::$indexIds);

            $model->decrypt(
                $model->attributes
            );
        });
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     */
    public function encrypt(array $attributes = ['*']): self
    {
        static::$indexIds = [];

        foreach ($this->getEnigmaEncryptable() as $column) {
            if (isset($attributes[$column])) {
                $this->encryptWithIndexes($this->id, $column);
            }
        }

        foreach ($this->getLaravelEncryptable() as $column) {
            if (isset($attributes[$column])) {
                $this->{$column} = encrypt($this->{$column});
            }
        }

        return $this;
    }

    /**
     * @param int $id
     * @param string $column
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     */
    private function encryptWithIndexes(?int $id, string $column): void
    {
        if ($id !== null) {
            (app()->makeWith(DBInterface::class, [
                'table' => $this->getTable(),
            ]))->deleteHash($id, $column);
        }

        [$value, $hashes] = $this->engine->encryptWithIndexes(
            $this,
            $column,
            $this->{$column}
        );

        $this->{$column} = $value;

        if (count($hashes)) {
            foreach ($hashes as $idHash) {
                static::$indexIds[] = $idHash;
            }
        }
    }

    /**
     * @param string[] $attributes
     * @return $this
     * @throws CryptoOperationException
     */
    public function decrypt($attributes = ['*']): self
    {
        foreach ($this->getEnigmaEncryptable() as $column) {
            if (isset($attributes[$column])) {
                $this->{$column} = $this->engine->decrypt(
                    $this->getTable(),
                    $column,
                    $this->{$column}
                );
            }
        }

        foreach ($this->getLaravelEncryptable() as $column) {
            if (isset($attributes[$column])) {
                $this->{$column} = decrypt($this->{$column});
            }
        }

        return $this;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param QueryBuilder $query
     * @return EloquentBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }
}
