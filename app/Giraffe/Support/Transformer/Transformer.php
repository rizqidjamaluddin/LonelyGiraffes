<?php  namespace Giraffe\Support\Transformer;
interface Transformer
{
    /**
     * @param $transformable
     * @return array
     */
    public function transform($transformable);
} 