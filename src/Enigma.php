<?php

namespace Omatech\Enigma;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Omatech\Enigma\CipherSweet\Index;
use Omatech\Enigma\Database\Contracts\DBInterface;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\Backend\ModernCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException;
use ParagonIE\CipherSweet\Exception\BlindIndexNotFoundException;
use ParagonIE\CipherSweet\Exception\CryptoOperationException;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\ConstantTime\Hex;
use SodiumException;

const TABLE = 'table';

class Enigma
{
    private $engine;

    /**
     * Enigma constructor.
     * @throws CryptoOperationException
     */
    public function __construct()
    {
        $key = new StringProvider(
            substr(Hex::encode(env('APP_KEY')), 0, 64)
        );

        $backend = (config('enigma.backend') === 'fips') ? new FIPSCrypto() : new ModernCrypto();
        $this->engine = new CipherSweet($key, $backend);
    }

    /**
     * @param string $tableName
     * @param string $column
     * @param string $value
     * @return string
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    public function encrypt(string $tableName, string $column, string $value): string
    {
        [$value] = (new EncryptedField(
            $this->engine,
            $tableName,
            $column
        ))->prepareForStorage($value);

        return $value;
    }

    /**
     * @param Model $model
     * @param string $column
     * @param string $value
     * @return array
     * @throws BlindIndexNameCollisionException
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    public function encryptWithIndexes(Model $model, string $column, string $value): array
    {
        [$value, $indexes] = $this->createWithIndexes($model, $column, $value);

        $ids = [];

        if ($indexes) {
            foreach ($indexes[$column] as $index) {
                $ids[] = (app()->makeWith(DBInterface::class, [
                    TABLE => $model->getTable(),
                ]))->insertHash($column, $index);
            }
        }

        return [$value, $ids];
    }

    /**
     * @param Model $model
     * @param string $column
     * @param string $value
     * @param bool $strategies
     * @return array|EncryptedField
     * @throws BlindIndexNameCollisionException
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    private function createWithIndexes(Model $model, string $column, string $value, bool $strategies = true)
    {
        $tableName = $model->getTable();

        $field = new EncryptedField(
            $this->engine,
            $tableName,
            $column
        );

        return $this->createBlindIndexes($model, $column, $value, $field, $strategies);
    }

    /**
     * @param Model $model
     * @param string $column
     * @param string $value
     * @param EncryptedField $field
     * @param bool $strategies
     * @return array|EncryptedField
     * @throws BlindIndexNameCollisionException
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    private function createBlindIndexes(Model $model, string $column, string $value, EncryptedField $field, bool $strategies)
    {
        $hashes = [];
        $blindIndexMethod = $this->getBlindIndexMethod($column);

        if (method_exists($model, $blindIndexMethod)) {
            $index = new Index($column);
            $model->{$blindIndexMethod}($index);

            $blindIndex = new BlindIndex(
                $index->name,
                $index->transformers,
                $index->bits,
                $index->fast
            );
            $field->addBlindIndex($blindIndex);

            if ($strategies) {
                $hashes = $this->createBlindStrategies($field, $index, $blindIndex->getTransformed($value));
            }
        }

        [$value, $hash] = $field->prepareForStorage($value);

        $hashes[] = $hash[$column];
        $hash[$column] = $hashes;
        $hash[$column] = array_unique($hash[$column]);
        shuffle($hash[$column]);

        if (! $strategies) {
            return $field;
        }

        return [$value, $hash];
    }

    /**
     * @param EncryptedField $field
     * @param Index $index
     * @param string $value
     * @return array
     */
    public function createBlindStrategies(EncryptedField $field, Index $index, string $value): array
    {
        $hashes = last(array_map(static function ($strategy) use ($value, $field) {
            $strategy = $strategy->__invoke($value);
            if (is_array($strategy)) {
                return array_map(static function ($val) use ($field) {
                    return last(/** @scrutinizer ignore-type */$field->prepareForStorage($val)[1]);
                }, $strategy);
            }

            return [last(/** @scrutinizer ignore-type */$field->prepareForStorage($strategy)[1])];
        }, $index->strategies ?? []));

        return (! $hashes) ? [] : $hashes;
    }

    /**
     * @param Model $model
     * @param string $column
     * @param $value
     * @param bool $strategies
     * @return string|null
     * @throws BlindIndexNameCollisionException
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    public function getHash(Model $model, string $column, string $value, bool $strategies = true): ?string
    {
        $index = $this->createWithIndexes(
            $model,
            $column,
            $value,
            $strategies
        )->getAllBlindIndexes($value);

        return (empty($index)) ? null : $index[$column];
    }

    /**
     * @param string $tableName
     * @param string $column
     * @param string $value
     * @return Model
     * @throws CryptoOperationException
     */
    public function decrypt(string $tableName, string $column, string $value): string
    {
        return (new EncryptedField(
            $this->engine,
            $tableName,
            $column
        ))->decryptValue($value);
    }

    /**
     * @param $model
     * @param $modelId
     * @param $column
     */
    public function deleteHash($model, $modelId, $column): void
    {
        (app()->makeWith(DBInterface::class, [
            TABLE => $model->getTable(),
        ]))->deleteHash($modelId, $column);
    }

    /**
     * @param $model
     * @param $modelId
     * @param $ids
     */
    public function setModelId($model, $modelId, $ids): void
    {
        (app()->makeWith(DBInterface::class, [
            TABLE => $model->getTable(),
        ]))->setModelId($modelId, $ids);
    }

    /**
     * @param string $column
     * @return string
     */
    private function getBlindIndexMethod(string $column): string
    {
        return lcfirst(str_replace('_', '', Str::title($column))).'BlindIndex';
    }
}
