<?php  namespace Giraffe\Support\Transformer; 
interface Serializer 
{
    /**
     * @param array $data
     * @return mixed
     */
    public function process(Array $data);
}