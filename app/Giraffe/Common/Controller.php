<?php  namespace Giraffe\Common;


use App;
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


    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
    }

    public function withItem($item, Transformer $transformer = null, $key = null)
    {
        if (!$key) {
            $key = $this->key;
        }

        $presenter = new Presenter();
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