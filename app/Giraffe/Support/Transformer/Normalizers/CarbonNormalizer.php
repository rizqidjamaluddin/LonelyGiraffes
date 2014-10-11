<?php  namespace Giraffe\Support\Transformer\Normalizers;

use Carbon\Carbon;
use Giraffe\Support\Transformer\Normalizer;

class CarbonNormalizer extends Normalizer
{
    public function normalize($data)
    {

        array_walk_recursive(
            $data,
            function (&$value, $key) {
                if ($value instanceof Carbon) {
                    $value = (string) $value;
                }
            }
        );

        if ($this->next) {
            $this->next->normalize($data);
        }
        return $data;
    }
} 