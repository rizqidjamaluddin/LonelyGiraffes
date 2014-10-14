<?php  namespace Giraffe\Support\Transformer; 
interface Serializer 
{
    /**
     * @param array|TransformedEntity $data
     * @param array $meta
     * @return mixed
     */
    public function process($data, Array $meta);
}