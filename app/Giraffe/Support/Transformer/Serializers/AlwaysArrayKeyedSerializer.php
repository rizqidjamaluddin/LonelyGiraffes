<?php  namespace Giraffe\Support\Transformer\Serializers; 
use Giraffe\Support\Transformer\Serializer;
use Giraffe\Support\Transformer\TransformedEntity;

class AlwaysArrayKeyedSerializer implements Serializer
{

    public function process($data, Array $meta)
    {
        $data = $this->wrapSingleEntity($data);

        return [
            $meta['key'] => $data
        ];
    }

    /**
     * @param $data
     * @return array
     */
    protected function wrapSingleEntity($data)
    {
        if ($data instanceof TransformedEntity) {
            $data = [$data];
            return $data;
        }
        return $data;
    }
}