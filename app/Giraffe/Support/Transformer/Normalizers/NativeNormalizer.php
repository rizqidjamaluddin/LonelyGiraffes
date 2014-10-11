<?php namespace Giraffe\Support\Transformer\Normalizers;

use Giraffe\Support\Transformer\Normalizer;

/**
 * Class NativeNormalizer
 *
 * Basic normalizer that handles native PHP formats.
 *
 * @package Giraffe\Support\Transformer
 */
class NativeNormalizer extends Normalizer
{
    public function normalize($data)
    {
        if ($this->next) {
            $this->next->normalize($data);
        }
        return $data;
    }
}