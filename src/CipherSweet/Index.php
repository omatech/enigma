<?php

namespace Omatech\Enigma\CipherSweet;

class Index
{
    public $name;
    public $bits = 256;
    public $transformers = [];
    public $strategies = [];
    public $fast = true;

    /**
     * Index constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param array $transformers
     * @return Index
     */
    public function transformers(array $transformers): self
    {
        $this->transformers = $transformers;

        return $this;
    }

    /**
     * @param array $strategies
     * @return $this
     */
    public function strategies(array $strategies): self
    {
        $this->strategies = $strategies;

        return $this;
    }

    /**
     * @param int $bits
     * @return Index
     */
    public function bits(int $bits): self
    {
        $this->bits = $bits;

        return $this;
    }

    /**
     * @param bool $value
     * @return Index
     */
    public function fast(bool $value = true): self
    {
        $this->fast = $value;

        return $this;
    }
}
