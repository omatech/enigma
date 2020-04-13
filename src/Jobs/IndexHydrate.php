<?php

namespace Omatech\Enigma\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Omatech\Enigma\Enigma;

class IndexHydrate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $modelClass;
    private $modelId;

    /**
     * Create a new job instance.
     *
     * @param string $modelClass
     * @param int $modelId
     */
    public function __construct(string $modelClass, int $modelId)
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
    }

    /**
     * Execute the job.
     *
     * @param Enigma $enigma
     * @return void
     * @throws \ParagonIE\CipherSweet\Exception\BlindIndexNameCollisionException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     */
    public function handle(Enigma $enigma)
    {
        $model = (new $this->modelClass)::find($this->modelId);
        $enigmaEncryptable = $model->getEnigmaEncryptable();

        foreach ($enigmaEncryptable as $column) {
            if ($model->{$column} !== null) {
                $enigma->hydrateAsModel($model, $column, $model->{$column});
            }
        }
    }
}
