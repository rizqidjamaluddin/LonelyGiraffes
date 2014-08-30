<?php  namespace Giraffe\Common\Internal;

use Giraffe\Common\NotFoundModelException;
use Giraffe\Common\Repository;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class QueryFilter
{

    /**
     * @var Collection
     */
    protected $keys;

    public function __construct()
    {
        $this->keys = new Collection;
    }

    /**
     * Set a query filter value. Default will be used if the value is falsey. A repository can be given to the
     * $transform argument, which will use getByHash to fetch the real value instead of a hash. A callable can
     * also be passed to use the return value.
     *
     * The range parameter can be used to truncate an integer value within a [min,max] or [max] range, inclusive.
     *
     * @param                          $key
     * @param                          $value
     * @param mixed                    $default
     * @param Repository|Callable|null $transform
     * @param Array                    $range
     * @return mixed
     */
    public function set($key, $value, $default = null, $transform = null, Array $range = null)
    {

        if (!$value) {
            $value = $default;
        }

        $value = $this->cullToRange($value, $range);

        // if value is null, we don't need to try to transform it
        if (!is_null($value)) {
            if ($transform instanceof Repository) {
                return $this->transformByRepository($key, $value, $transform);
            }

            if (is_callable($transform)) {
                $this->keys[$key] = $transform($value);
                return $value;
            }
        }

        $this->keys[$key] = $value;
        return $value;
    }

    /**
     * Get a value out of the filter.
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->keys->get($key);
    }

    public function any()
    {
        return $this->keys->count() != 0;
    }

    /**
     * @param $value
     * @param $range
     * @return mixed
     */
    protected function cullToRange($value, $range)
    {
        if (is_integer($value)) {
            if (count($range) == 2) {
                $value = max($value, $range[0]);
                $value = min($value, $range[1]);
            }
            if (count($range) == 1) {
                $value = min($value, $range[0]);
                return $value;
            }
            return $value;
        }
        return $value;
    }

    /**
     * @param            $key
     * @param            $value
     * @param Repository $transform
     * @return \stdClass
     */
    protected function transformByRepository($key, $value, Repository $transform)
    {
        try {
            $value = $transform->getByHash($value);
            $this->keys[$key] = $value;
        } catch (NotFoundModelException $e) {
            throw new BadRequestHttpException("$value not found.");
        }
        return $value;
    }

} 