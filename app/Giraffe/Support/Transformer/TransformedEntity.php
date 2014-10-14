<?php  namespace Giraffe\Support\Transformer; 
use Illuminate\Support\Contracts\JsonableInterface;

class TransformedEntity implements JsonableInterface
{
    /**
     * @var array
     */
    private $entity;

    public function __construct(Array $entity)
    {

        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->getEntity());
    }

    public function __toString()
    {
        return $this->toJson();
    }
}