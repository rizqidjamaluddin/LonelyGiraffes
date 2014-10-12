<?php
use Giraffe\Common\Controller;
use Giraffe\Stickies\StickyService;
use Giraffe\Stickies\StickyTransformer;
use Giraffe\Support\Transformer\Presenter;
use Giraffe\Support\Transformer\Serializers\AlwaysArrayKeyedSerializer;

class StickiesController extends Controller
{

    /**
     * @var Giraffe\Stickies\StickyService
     */
    private $service;

    public function __construct(StickyService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    public function index()
    {
        $stickies = $this->service->getStickies();
        $presenter = new Presenter();
        return $presenter->setSerializer(new AlwaysArrayKeyedSerializer('stickies'))
                         ->transform($stickies, new StickyTransformer());
    }
}