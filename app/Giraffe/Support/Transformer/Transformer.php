<?php namespace Giraffe\Support\Transformer;

abstract class Transformer
{
    /**
     * @param $entity
     * @return array
     */
    public abstract function transform($entity);
}