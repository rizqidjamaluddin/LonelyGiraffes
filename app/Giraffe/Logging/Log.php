<?php  namespace Giraffe\Logging;

use Giraffe\Common\ConfigurationException;
use Illuminate\Foundation\Application;
use Illuminate\Log\Writer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;

/**
 * @method bool debug($message, $meta = [])
 * @method bool info($message, $meta = [])
 * @method bool notice($message, $meta = [])
 * @method bool warning($message, $meta = [])
 * @method bool error($message, $meta = [])
 * @method bool critical($message, $meta = [])
 * @method bool alert($message, $meta = [])
 * @method bool emergency($message, $meta = [])
 */
class Log
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function __call($method, Array $arguments = [])
    {
        $message = $arguments[0];

        if (isset($arguments[1])) {
            $context = $arguments[1];
        } else {
            $context = [];
        }
        if (!is_array($context)) {
            $context = [$context];
        }
        return call_user_func([$this->logger, $method], $message, $context);
    }

} 