<?php

namespace Omatech\Enigma\Tests\Stubs\Strategies;

use Omatech\Enigma\Strategies\Contracts\StrategyInterface;

class EmailByDomain implements StrategyInterface
{
    /**
     * @param string $input
     * @return string
     */
    public function __invoke(string $input)
    {
        return last(explode('@', $input));
    }
}
