<?php  namespace Giraffe\Support\Transformer;

use ArrayObject;
use Giraffe\Common\MyBadException;
use Giraffe\Support\Transformer\Serializers\NullSerializer;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;
use Illuminate\Support\Collection;

class Presenter
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Array
     */
    protected $metadata = [];

    /**
     * @param Normalizer $normalizer
     * @param Serializer $serializer
     */
    public function __construct(Normalizer $normalizer = null, Serializer $serializer = null)
    {
        if (!$normalizer) {
            $normalizer = new NativeNormalizer();
        }
        $this->normalizer = $normalizer;

        if (!$serializer) {
            $serializer = new NullSerializer();
        }
        $this->serializer = $serializer;
    }

    /**
     * Transform a Transformable entity as defined by a compatible Transformer, and then formats it for output. If
     * the entity implements DefaultTransformable and no $transformer is given,
     * the default transformer is used instead.
     *
     * @param Transformable|array|ArrayObject|Collection $transformable
     * @param Transformer                                $transformer
     *
     * @throws \Giraffe\Common\MyBadException
     * @return array
     */
    public function transform($transformable, Transformer $transformer = null)
    {
        $transformer = $this->obtainDefaultTransformer($transformable, $transformer);
        $this->catchMissingTransformer($transformer);
        $this->catchUntransformable($transformable);

        if ($this->checkCollection($transformable)) {
            $result = [];
            foreach ($transformable as $entity) {
                $normalized = $this->handleEntity($entity, $transformer);
                $result[] = $normalized;
            }
        } else {
            $result = $this->handleEntity($transformable, $transformer);
        }

        $serialized = $this->serializer->process($result, $this->metadata);
        $finished = $this->normalizeTransformedEntities($serialized);

        return $finished;
    }

    /**
     * @param             $entity
     * @param Transformer $transformer
     *
     * @return TransformedEntity
     */
    public function handleEntity($entity, Transformer $transformer)
    {
        $this->checkCompatibility($entity, $transformer);
        $transformed = $transformer->transform($entity);
        $normalized = $this->normalizer->normalize($transformed);
        return new TransformedEntity($normalized);
    }

    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    public function setMeta($key, $content)
    {
        $this->metadata[$key] = $content;
        return $this;
    }

    /**
     * @param             $transformable
     * @param Transformer $transformer
     *
     * @throws \Giraffe\Common\MyBadException
     */
    protected function checkCompatibility($transformable, Transformer $transformer)
    {
        if (!$transformer->canServe($transformable)) {
            $transformerClass = get_class($transformer);
            $transformableClass = get_class($transformable);
            throw new MyBadException("Invalid transformer ($transformerClass) provided for entity
            ($transformableClass).");
        }
    }

    /**
     * @param $transformable
     *
     * @return bool
     */
    public function checkCollection($transformable)
    {
        return $transformable instanceof ArrayObject ||
        $transformable instanceof Collection ||
        is_array($transformable);
    }

    /**
     * @param $transformable
     *
     * @throws \Giraffe\Common\MyBadException
     */
    public function catchUntransformable($transformable)
    {
        if (!$transformable instanceof Transformable && !$this->checkCollection($transformable)) {
            $transformableClass = get_class($transformable);
            throw new MyBadException("Un-transformable entity ($transformableClass) passed for transformation.");
        }
    }

    /**
     * @param Transformer $transformer
     *
     * @throws \Giraffe\Common\MyBadException
     */
    public function catchMissingTransformer(Transformer $transformer)
    {
        if (!$transformer) {
            throw new MyBadException("No transformer provided for transformation.");
        }
    }

    /**
     * @param             $transformable
     * @param Transformer $transformer
     *
     * @return Transformer
     */
    public function obtainDefaultTransformer($transformable, Transformer $transformer = null)
    {
        if (!$transformer && $transformable instanceof DefaultTransformable) {
            $transformer = $transformable->getDefaultTransformer();
        }

        if (!$transformer && $this->checkCollection($transformable)) {
            if ($transformable[0] instanceof DefaultTransformable) {
                $transformer = $transformable[0]->getDefaultTransformer();
                return $transformer;
            }
            return $transformer;
        }
        return $transformer;
    }

    /**
     * @return mixed
     */
    protected function normalizeTransformedEntities($serialized)
    {
        array_walk_recursive(
            $serialized,
            function (&$entity) {
                if ($entity instanceof TransformedEntity) {
                    $entity = $entity->getEntity();
                }
            }
        );
        return $serialized;
    }

} 