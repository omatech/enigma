<?php

namespace Omatech\Enigma\Strategies;

use Omatech\Enigma\Strategies\Contracts\StrategyInterface;

class LikeSearch implements StrategyInterface
{
    /**
     * @param string $input
     * @return array
     */
    public function __invoke(string $input): array
    {
        $possibilities = [];
        $len = strlen($input);

        for ($i = 0; $i <= $len; $i++) {
            for ($j = 0; $j <= $len; $j++) {
                $possibilities[] = (string) substr($input, $i, $j);
            }
        }

        return array_filter(array_unique($possibilities));
    }
}
