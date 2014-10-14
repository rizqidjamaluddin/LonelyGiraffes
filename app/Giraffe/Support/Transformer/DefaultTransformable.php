<?php  namespace Giraffe\Support\Transformer; 
interface DefaultTransformable 
{
    /**
     * @return Transformer
     */
    public function getDefaultTransformer();
} 