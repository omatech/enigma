<?php

namespace Omatech\Enigma\Commands;

use RuntimeException;
use Omatech\Enigma\Enigma;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Omatech\Enigma\Database\Eloquent\HasEnigma;

class ReIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enigma:reindex { model : Fully qualified model class name }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rehydratate the indexes given a Model';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Enigma $enigma)
    {
        $model = $this->argument('model');
        $model = new $model;

        if (isset(class_uses($model)[HasEnigma::class]) === false ||
            ($model instanceof Model) === false) {
            throw new RuntimeException('The given class is not an instance of a Model or has not Enigma loaded');
        }

        $enigmaEncryptable = $model->getEnigmaEncryptable();

        $model::chunk(100, function ($rows) use ($enigma, $model, $enigmaEncryptable) {
            foreach ($rows as $row) {
                $ids = [];

                foreach ($enigmaEncryptable as $column) {
                    $enigma->deleteHash($model, $row->id, $column);

                    if ($row->{$column} !== null) {
                        $hashes = $enigma->encryptWithIndexes($model, $column, $row->{$column})[1];
                        $ids = array_merge($ids, $hashes);
                    }
                }

                $enigma->setModelId($model, $row->id, $ids);
            }
        });
    }
}
