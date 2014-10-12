<?php
use Giraffe\Support\Transformer\Normalizers\CarbonNormalizer;
use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;

class TransformerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_normalize_a_simple_array_of_string_arrays()
    {
        $test = [
            'foo' => 'lorem',
            'bar' => 'ipsum'
        ];

        $subject = new NativeNormalizer();
        $normalized = $subject->normalize($test);

        $this->assertEquals('lorem', $normalized['foo']);
        $this->assertEquals('ipsum', $normalized['bar']);
    }

    /**
     * @test
     */
    public function it_formats_numbers()
    {
        $test = [
            'integer'          => 100,
            'big_integer'      => 1000 * 1000,
            'negative'         => -100,
            'big_negative'     => 0 - (1000 * 1000),
            'decimal'          => 3.14,
            'negative_decimal' => -3.14
        ];

        $subject = new NativeNormalizer();
        $normalized = $subject->normalize($test);

        $this->assertTrue(100 === $normalized['integer']);
        $this->assertTrue(1000000 === $normalized['big_integer']);
        $this->assertTrue(-100 === $normalized['negative']);
        $this->assertTrue(-1000000 === $normalized['big_negative']);
        $this->assertTrue(3.14 === $normalized['decimal']);
        $this->assertTrue(-3.14 === $normalized['negative_decimal']);
    }

    /**
     * @test
     */
    public function it_formats_booleans()
    {
        $test = [
            'true'  => true,
            'false' => false,
        ];

        $subject = new NativeNormalizer();
        $normalized = $subject->normalize($test);

        $this->assertTrue(true === $normalized['true']);
        $this->assertTrue(false === $normalized['false']);
    }

    /**
     * @test
     */
    public function it_formats_carbon_dates_with_the_carbon_normalizer()
    {
        $time = '2012-12-25 09:05:59';
        $test = [
            'now' => new \Carbon\Carbon($time)
        ];

        $subject = new CarbonNormalizer(new NativeNormalizer());
        $normalized = $subject->normalize($test);

        $this->assertTrue($time === $normalized['now']);
    }

    /**
     * @test
     */
    public function it_recursively_formats_embedded_arrays()
    {
        $test = [
            'one' => [
                'string' => 'foo',
                'number' => 1,
                'time'   => new \Carbon\Carbon("2000-01-01 12:00:00")
            ],
            'two' => [
                'string' => 'bar',
                'number' => 5,
                'time'   => new \Carbon\Carbon("2010-01-01 12:00:00")
            ]
        ];

        $subject = new CarbonNormalizer(new NativeNormalizer());
        $normalized = $subject->normalize($test);

        $this->assertTrue('foo' === $normalized['one']['string']);
        $this->assertTrue(1 === $normalized['one']['number']);
        $this->assertTrue("2000-01-01 12:00:00" === $normalized['one']['time']);
        $this->assertTrue('bar' === $normalized['two']['string']);
        $this->assertTrue(5 === $normalized['two']['number']);
        $this->assertTrue("2010-01-01 12:00:00" === $normalized['two']['time']);
    }

    public function it_can_use_the_always_collection_json_output_serializer()
    {

    }
}