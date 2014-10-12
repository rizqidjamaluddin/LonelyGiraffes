<?php  namespace Giraffe\Support\Transformer\Serializers;

use Giraffe\Support\Transformer\Serializer;

/**
 * Do nothing with this serializer. Also the default one if not given.
 */
class NullSerializer implements Serializer
{

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function process(Array $data)
    {
        return $data;
    }
}