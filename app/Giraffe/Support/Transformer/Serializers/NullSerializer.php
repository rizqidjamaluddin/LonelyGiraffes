<?php  namespace Giraffe\Support\Transformer\Serializers;

use Giraffe\Support\Transformer\Serializer;

/**
 * Do nothing with this serializer. Also the default one if not given.
 */
class NullSerializer implements Serializer
{

    public function process($data, Array $meta)
    {
        return $data;
    }
}