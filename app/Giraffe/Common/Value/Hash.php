<?php  namespace Giraffe\Common\Value;

use Illuminate\Support\Str;

/**
 * Generic class for creating hashes.
 *
 * @package Giraffe\Common
 */
class Hash
{

    /**
     * @var string
     */
    protected $value;

    public function __construct()
    {
        /** @var Str $generator */
        $generator = \App::make(Str::class);
        $this->value = $generator->quickRandom(32);

        return $this;
    }

    function __toString()
    {
        return $this->value;
    }

} 