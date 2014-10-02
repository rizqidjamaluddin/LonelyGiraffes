<?php  namespace Giraffe\Common;

interface Transformable
{
    /**
     * @return Transformer
     */
    public function getTransformer();
} 