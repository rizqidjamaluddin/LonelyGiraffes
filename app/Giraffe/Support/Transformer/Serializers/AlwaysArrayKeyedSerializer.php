<?php  namespace Giraffe\Support\Transformer\Serializers; 
use Giraffe\Support\Transformer\Serializer;

class AlwaysArrayKeyedSerializer implements Serializer
{

    /**
     * @var string
     */
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function process(Array $data)
    {
        return [
            $this->key => $data
        ];
    }}