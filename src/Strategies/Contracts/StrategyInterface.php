<?php

namespace Omatech\Enigma\Strategies\Contracts;

interface StrategyInterface
{
    public function __invoke($value);
}
