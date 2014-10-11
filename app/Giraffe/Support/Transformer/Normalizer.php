<?php namespace Giraffe\Support\Transformer;

abstract class Normalizer
{
    /**
     * @var Normalizer
     */
    protected $next;

    public function __construct(Normalizer $normalizer = null)
    {
        if ($normalizer) {
            $this->next = $normalizer;
        }
    }

    abstract public function normalize($data);
}