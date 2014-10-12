<?php  namespace Giraffe\Support\Transformer;

use Giraffe\Support\Transformer\Transformable;
use Giraffe\Support\Transformer\Transformer;
use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;

class Presenter
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    /**
     * @param Normalizer $normalizer
     */
    public function __construct(Normalizer $normalizer = null)
    {
        if (!$normalizer) {
            $normalizer = new NativeNormalizer();
        }
        $this->normalizer = $normalizer;
    }

    /**
     * Transform a Transformable entity as defined by a compatible Transformer, and then formats it for output. If
     * the entity implements DefaultTransformable and no $transformer is given,
     * the default transformer is used instead.
     *
     * @param Transformable $transformable
     * @param Transformer   $transformer
     *
     * @return array
     */
    public function transform(Transformable $transformable, Transformer $transformer = null)
    {
        if (!$transformer && $transformable instanceof DefaultTransformable) {
            $transformer = $transformable->getDefaultTransformer();
        }

        $transformed = $transformer->transform($transformable);
        $normalized = $this->normalizer->normalize($transformed);

        return $normalized;
    }
} 