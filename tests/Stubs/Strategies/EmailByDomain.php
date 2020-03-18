<?php

namespace Omatech\Enigma\Tests\Stubs\Strategies;

use Omatech\Enigma\Strategies\Contracts\StrategyInterface;

class EmailByDomain implements StrategyInterface
{
    /**
     * @param $input
     * @return string
     */
    public function __invoke($input)
    {
        return last(explode('@', $input));
    }
}
