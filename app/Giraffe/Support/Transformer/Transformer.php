<?php  namespace Giraffe\Support\Transformer;
abstract class Transformer
{
    /**
     * @param $transformable
     * @return array
     */
    public function transform($transformable) {
        return (array) $transformable;
    }

    public function canServe($entity) {
        if ($this->getServedClass()) {
           return is_a($entity, $this->getServedClass());
        }
        return true;
    }

    public function getServedClass()
    {
        return null;
    }
} 