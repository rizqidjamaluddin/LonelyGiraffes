<?php  namespace Giraffe\Common;


use App;
use Giraffe\Support\Transformer\Presenter;
use Giraffe\Support\Transformer\Serializers\AlwaysArrayKeyedSerializer;
use Giraffe\Support\Transformer\Transformer;
use Illuminate\Http\Response;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class Controller extends \BaseController
{
    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;


    public function __construct()
    {
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
    }

    /**
     *
     * Wrapper for dingo/api's response builder class until feature is implemented. Returns one model, through a
     * transformer, with a key.
     *
     * @see https://github.com/dingo/api/issues/94
     *
     * @param        $item
     * @param        $transformer
     * @param string $key
     *
     * @return Response
     */
    public function withItem($item, Transformer $transformer, $key = 'data')
    {
        $presenter = new Presenter();
        $data = $presenter->setSerializer(new AlwaysArrayKeyedSerializer)
                         ->setMeta('key', $key)
                         ->transform($item, $transformer);
        return new Response($data, 200, []);
    }

    public function withCollection($collection, Transformer $transformer, $key = 'data')
    {
        return $this->withItem($collection, $transformer, $key);
    }

} 