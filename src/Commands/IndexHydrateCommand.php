<?php

namespace Omatech\Enigma\Commands;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Omatech\Enigma\Database\Eloquent\HasEnigma;
use Omatech\Enigma\Enigma;
use Omatech\Enigma\Exceptions\InvalidClassException;

class IndexHydrateCommand extends Command
{
    private $enigma;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enigma:hydrate { namespace : Fully qualified namespace }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rehydrate the indexes given a namespace';

    public function __construct(Enigma $enigma)
    {
        parent::__construct();
        $this->enigma = $enigma;
    }

    /**
     * Execute the console command.
     *
     * @param Enigma $enigma
     * @return mixed
     * @throws InvalidClassException
     */
    public function handle()
    {
        $namespace = (string) $this->argument('namespace');
        $foundClasses = ClassFinder::getClassesInNamespace($namespace, ClassFinder::RECURSIVE_MODE);

        $classes = [];
        foreach ($foundClasses as $class) {
            if (isset(class_uses($class)[HasEnigma::class]) === true) {
                $classes[] = $class;
            }
        }

        $choice = (array) $this->choice(
            'Which models would you like to hydrate?',
            array_merge([0 => 'All'], $classes),
            0,
            null,
            true
        );

        if (array_search('All', $choice) !== false) {
            $choice = $classes;
        }

        foreach ($choice as $class) {
            $this->hydrate(new $class);
        }

        $this->info('The index hydratation has been finished.');
    }

    private function hydrate(Model $model)
    {
        $enigmaEncryptable = $model->getEnigmaEncryptable();

        $this->info(get_class($model)."\n");
        $bar = $this->output->createProgressBar((int) $model::count());
        $bar->start();

        $model::chunk(100, function ($rows) use ($enigmaEncryptable, $bar) {
            foreach ($rows as $row) {
                foreach ($enigmaEncryptable as $column) {
                    if ($row->{$column} !== null) {
                        $this->enigma->hydrateAsModel($row, $column, $row->{$column});
                    }
                }
                $bar->advance();
            }
        });
        $bar->finish();
        $this->info("\n");
    }
}
