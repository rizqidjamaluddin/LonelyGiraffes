<?php  namespace Giraffe\Common;


use App;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;

class Controller extends \Dingo\Api\Routing\Controller
{
    /**
     * @var \Dingo\Api\Auth\Shield
     */
    protected $auth;
    /**
     * @var \Giraffe\Authorization\Gatekeeper
     */
    protected $gatekeeper;

    /**
     * @var \Dingo\Api\Http\ResponseBuilder
     */
    protected $response;

    public function __construct()
    {
        $api = App::make('Dingo\Api\Dispatcher');
        $this->auth = App::make('Dingo\Api\Auth\Shield');
        $this->gatekeeper = App::make('Giraffe\Authorization\Gatekeeper');
        $this->response = App::make('Dingo\Api\Http\ResponseBuilder');

         parent::__construct($api, $this->auth, $this->response);

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
    public function withItem($item, TransformerAbstract $transformer, $key = 'data')
    {
        if ($item) {
            $resource = new Collection([$item], $transformer, $key);
        } else {
            $resource = new Collection([], $transformer, $key);
        }

        /** @var Manager $fractal */
        $fractal = App::make('dingo.api.transformer')->getFractal();
    $resource = $fractal->createData($resource)->toArray();
        return new Response($resource, 200, []);
    }

    public function withCollection($collection, TransformerAbstract $transformer, $key = 'data')
    {
        $resource = new Collection($collection, $transformer, $key);
        $resource = App::make('dingo.api.transformer')->getFractal()->createData($resource)->toArray();
        return new Response($resource, 200, []);
    }

} 