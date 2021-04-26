<?php

namespace Omatech\Enigma\Database\Eloquent;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Omatech\Enigma\Database\Eloquent\Builder as EloquentBuilder;
use Omatech\Enigma\Enigma;
use Omatech\Enigma\Exceptions\RepeatedAttributesException;
use Omatech\Enigma\Jobs\IndexHydrate;
use ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException;
use ParagonIE\CipherSweet\Exception\CryptoOperationException;
use SodiumException;
use Throwable;

trait HasEnigma
{
    private $engine;

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
            if (!empty($model->getEnigmaEncryptable())) {
                dispatch(new IndexHydrate(get_class($model), $model->id))
                    ->onQueue('enigma');
            }

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
        foreach ($this->getEnigmaEncryptable() as $column) {
            if (isset($attributes[$column])) {
                $this->{$column} = $this->engine->encrypt(
                    $this->getTable(),
                    $column,
                    $this->{$column}
                );
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
