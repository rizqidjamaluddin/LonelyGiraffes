<?php  namespace Giraffe\Support\Transformer;

use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;

class Presenter
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    public function __construct(Normalizer $normalizer = null)
    {
        if (!$normalizer) {
            $normalizer = new NativeNormalizer();
        }
        $this->normalizer = $normalizer;
    }
} 