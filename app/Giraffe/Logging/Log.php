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
 * @method bool debug($stream, $message, $meta = '')
 * @method bool info($stream, $message, $meta = '')
 * @method bool notice($stream, $message, $meta = '')
 * @method bool warning($stream, $message, $meta = '')
 * @method bool error($stream, $message, $meta = '')
 * @method bool critical($stream, $message, $meta = '')
 * @method bool alert($stream, $message, $meta = '')
 * @method bool emergency($stream, $message, $meta = '')
 */
class Log
{
    protected $levels = [
        'debug',
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'alert',
        'emergency'
    ];

    /**
     * @var Logger
     */
    protected $monolog;

    /**
     * @var Logger[]
     */
    protected $channels;


    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var \Illuminate\Foundation\Application
     */
    private $application;

    public function __construct(Writer $writer, Application $application)
    {
        $this->writer = $writer;
        $this->application = $application;
        $this->defaultLogLevel = Logger::DEBUG;
        $this->exceptionalLogLevel = Logger::WARNING;
    }

    public function boot()
    {
        $this->monolog = $this->writer->getMonolog();

    }

    /**
     * Methods come in this format:
     * $log->warning('sourceClass', []);
     *
     * The second array can be any additional context to attach with the request.
     *
     * @param       $method
     * @param array $arguments
     *
     * @return bool
     * @throws \Giraffe\Common\ConfigurationException
     */
    public function __call($method, Array $arguments)
    {
    }

    /**
     * @param $raw_level
     *
     * @returns integer
     * @throws \Giraffe\Common\ConfigurationException
     */
    protected function translateLogLevel($raw_level)
    {
        $raw_level = strtolower($raw_level);

        if (in_array($raw_level, $this->levels)) {
            return strtoupper($raw_level);
        }
        throw new ConfigurationException("Invalid log level used ($raw_level).");
    }
} 