<?php  namespace Giraffe\Common\Value;

use Illuminate\Support\Contracts\ArrayableInterface;

class ApiAction implements ArrayableInterface
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $label;

    public function __construct($url, $method, $label)
    {
        $this->url = $url;
        $this->method = $method;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function toArray()
    {
        return [
            'method' => $this->getMethod(),
            'url' => url($this->getUrl()),
            'label' => $this->getLabel()
        ];
    }
} 