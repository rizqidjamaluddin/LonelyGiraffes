<?php
use Giraffe\Common\Controller;
use Giraffe\Stickies\StickyService;
use Giraffe\Stickies\StickyTransformer;
use Giraffe\Support\Transformer\Presenter;
use Giraffe\Support\Transformer\Serializers\AlwaysArrayKeyedSerializer;

class StickiesController extends Controller
{

    protected $key = 'stickies';

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
        return $this->withCollection($stickies, new StickyTransformer());
    }
}