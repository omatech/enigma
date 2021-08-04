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
            substr(Hex::encode(config('app.key')), 0, 64)
        );

        $backend = (config('enigma.backend') === 'fips') ? new FIPSCrypto() : new ModernCrypto();
        $this->engine = new CipherSweet($key, $backend);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $value
     * @return string
     * @throws BlindIndexNotFoundException
     * @throws CryptoOperationException
     * @throws SodiumException
     */
    public function encrypt(string $tableName, string $columnName, string $value): string
    {
        [$value] = (new EncryptedField(
            $this->engine,
            $tableName,
            $columnName
        ))->prepareForStorage($value);

        return $value;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $value
     * @return Model
     * @throws CryptoOperationException
     */
    public function decrypt(string $tableName, string $columnName, string $value): string
    {
        return (new EncryptedField(
            $this->engine,
            $tableName,
            $columnName
        ))->decryptValue($value);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $value
     * @param Index $index
     * @return int|string
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    public function search(string $tableName, string $columnName, string $value, Index $index)
    {
        $hash = $this->createHash($tableName, $columnName, $value, $index);

        $ids = (app()->makeWith(DBInterface::class, [
            TABLE => $tableName,
        ]))->findByHash($columnName, $hash);

        return (count($ids) === 0) ? -1 : implode(',', $ids);
    }

    /**
     * @param Model $model
     * @param string $columnName
     * @param string $value
     * @return int|string|null
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    public function searchAsModel(Model $model, string $columnName, string $value)
    {
        $blindIndexMethod = $this->getBlindIndexMethod($columnName);

        if (method_exists($model, $blindIndexMethod)) {
            $index = new Index;
            $index->name($columnName);

            $model->{$blindIndexMethod}($index);

            return $this->search($model->getTable(), $columnName, $value, $index);
        }

        return -1;
    }

    /**
     * @param Model $model
     * @param string $columnName
     * @param string $value
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    public function hydrateAsModel(Model $model, string $columnName, string $value): void
    {
        $blindIndexMethod = $this->getBlindIndexMethod($columnName);

        if (method_exists($model, $blindIndexMethod)) {
            (app()->makeWith(DBInterface::class, [
                TABLE => $model->getTable(),
            ]))->deleteHashes($model->id, $columnName);
            
            $index = new Index;
            $index->name($columnName);
            $model->{$blindIndexMethod}($index);

            $hashes = $this->createHashes($model->getTable(), $columnName, $value, $index);
            $hashes = array_unique($hashes);
            shuffle($hashes);

            (app()->makeWith(DBInterface::class, [
                TABLE => $model->getTable(),
            ]))->insertHashes($model->id, $columnName, $hashes);
        }
    }

    /**
     * @param $tableName
     * @param $columnName
     * @param $index
     * @return array
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    private function transform(string $tableName, string $columnName, Index $index): array
    {
        $field = new EncryptedField(
            $this->engine,
            $tableName,
            $columnName
        );

        $index->name($columnName);

        $blindIndex = new BlindIndex(
            $index->name,
            $index->transformers,
            $index->bits,
            $index->fast
        );

        $field->addBlindIndex($blindIndex);

        return [$field, $blindIndex];
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $value
     * @param Index $index
     * @return mixed
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    private function createHash(string $tableName, string $columnName, string $value, Index $index)
    {
        [$field] = $this->transform($tableName, $columnName, $index);

        return $field->prepareForStorage($value)[1][$index->name];
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $value
     * @param Index $index
     * @return array
     * @throws BlindIndexNameCollisionException
     * @throws CryptoOperationException
     */
    private function createHashes(string $tableName, string $columnName, string $value, Index $index): array
    {
        [$field, $blindIndex] = $this->transform($tableName, $columnName, $index);

        $value = $blindIndex->getTransformed($value);

        $hash = [$field->prepareForStorage($value)[1][$index->name]];

        $hashes = array_map(static function ($strategy) use ($value, $field, $index) {
            $strategy = $strategy->__invoke($value);

            if (is_array($strategy)) {
                return array_map(static function ($val) use ($field, $index) {
                    return $field->prepareForStorage($val)[1][$index->name];
                }, $strategy ?? []);
            }

            return [$field->prepareForStorage($strategy)[1][$index->name]];
        }, $index->strategies ?? []);

        if (count($hashes)) {
            $hashes = call_user_func_array('array_merge', $hashes);
        }

        return array_merge($hash, $hashes);
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
