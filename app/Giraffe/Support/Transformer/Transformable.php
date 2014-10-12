<?php  namespace Giraffe\Support\Transformer;

use Giraffe\Support\Transformer\Transformer;

interface Transformable
{
    /**
     * @return Transformer
     */
    public function getTransformer();
} 