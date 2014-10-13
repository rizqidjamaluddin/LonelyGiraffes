<?php
use Giraffe\Support\Transformer\Normalizers\CarbonNormalizer;
use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;
use Giraffe\Support\Transformer\Presenter;
use Giraffe\Support\Transformer\Serializers\AlwaysArrayKeyedSerializer;

class TransformerTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

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

    /**
     * @test
     */
    public function it_can_use_the_always_collection_json_output_serializer()
    {
        $transformed = [
            'string' => 'foo',
            'integer' => 10
        ];

        $transformed2 = [
            'string' => 'bar',
            'integer' => 50
        ];

        $entity = Mockery::mock(Giraffe\Support\Transformer\Transformable::class);
        $transformer = Mockery::mock(Giraffe\Support\Transformer\Transformer::class);
        $transformer->shouldReceive('canServe')->withAnyArgs()->andReturn(true);
        $transformer->shouldReceive('transform')->withAnyArgs()->times(1)->andReturn($transformed);

        $subject = new Presenter();
        $subject->setSerializer(new AlwaysArrayKeyedSerializer());
        $subject->setMeta('key', 'test');
        $result = $subject->transform($entity, $transformer);

        $this->assertEquals(1, count($result));
        $this->assertTrue(isset($result['test']));
        $this->assertEquals('foo', $result['test'][0]['string']);
        $this->assertEquals(10, $result['test'][0]['integer']);

        $transformer->shouldReceive('transform')->withAnyArgs()->times(2)->andReturn($transformed, $transformed2);

        // test collection
        $result = $subject->transform([$entity, $entity], $transformer);
        $this->assertEquals(1, count($result));
        $this->assertTrue(isset($result['test']));
        $this->assertEquals('foo', $result['test'][0]['string']);
        $this->assertEquals(10, $result['test'][0]['integer']);
        $this->assertEquals('bar', $result['test'][1]['string']);
        $this->assertEquals(50, $result['test'][1]['integer']);

    }
}