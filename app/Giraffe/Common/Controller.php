<?php  namespace Giraffe\Common;


use App;
use Giraffe\Logging\Log;
use Giraffe\Support\Transformer\Normalizers\CarbonNormalizer;
use Giraffe\Support\Transformer\Normalizers\NativeNormalizer;
use Giraffe\Support\Transformer\Presenter;
use Giraffe\Support\Transformer\Serializers\AlwaysArrayKeyedSerializer;
use Giraffe\Support\Transformer\Transformer;
use Illuminate\Http\Response;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class Controller extends \Controller
{
    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;

    protected $key = 'data';

    /**
     * @var Log
     */
    protected $log;


    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $this->log = App::make(Log::class);
    }

    public function withItem($item, Transformer $transformer = null, $key = null)
    {
        if (!$key) {
            $key = $this->key;
        }

        $presenter = new Presenter(new CarbonNormalizer(new NativeNormalizer()));
        $data = $presenter->setSerializer(new AlwaysArrayKeyedSerializer)
                         ->setMeta('key', $key)
                         ->transform($item, $transformer);
        return new Response($data, 200, []);
    }

    public function withCollection($collection, Transformer $transformer = null, $key = null)
    {
        return $this->withItem($collection, $transformer, $key);
    }

} 