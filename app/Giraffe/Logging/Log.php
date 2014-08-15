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
 * @method bool debug($stream, $context, $meta = '')
 * @method bool info($stream, $context, $meta = '')
 * @method bool notice($stream, $context, $meta = '')
 * @method bool warning($stream, $context, $meta = '')
 * @method bool error($stream, $context, $meta = '')
 * @method bool critical($stream, $context, $meta = '')
 * @method bool alert($stream, $context, $meta = '')
 * @method bool emergency($stream, $context, $meta = '')
 */
class Log
{
    protected $logInTests = false;

    protected $path = '/app-logs';

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

    protected $defaultLogLevel;
    protected $exceptionalLogLevel;

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
        $this->writer->useDailyFiles(storage_path() . '/logs/lg.log');
        $this->monolog = $this->writer->getMonolog();


        $this->channels = [];

        // establish general channels
        $request = new Logger('requests');
        $request->pushHandler(
            new StreamHandler(storage_path() . $this->path . "/requests/" . date('Y-m-d') . '.log')
        );
        $this->channels['request'] = $request;

        $authentication = new Logger('authentication');
        $authentication->pushHandler(
            new StreamHandler(storage_path() . $this->path . "/authentication/" . date('Y-m-d') . '.log')
        );
        $this->channels['authentication'] = $authentication;

        $authorization = new Logger('authorization');
        $authorization->pushHandler(
            new StreamHandler(storage_path() . $this->path . "/authorization/" . date('Y-m-d') . '.log')
        );
        $this->channels['authorization'] = $authorization;

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
        if (count($arguments) < 2) {
            throw new \BadMethodCallException;
        }

        if (is_object($arguments[0])) {
            $stream = get_class($arguments[0]);
        } else {
            $stream = $arguments[0];
        }

        $message = (string) $arguments[1];

        $context = isset($arguments[2]) ? $arguments[2] : [];
        if (!is_array($context)) {
            $context = [$context];
        }

        $level = $this->translateLogLevel($method);

        // disable logging if system is under testing environment
        if ($this->application->environment('testing') && !$this->logInTests) {
            return false;
        }

        if (!in_array($stream, $this->channels)) {
            $this->registerNewStream($stream);
        }

        // attach Gatekeeper current user if possible
        $currentUser = \App::make('Giraffe\Authorization\Gatekeeper')->me();
        if ($currentUser) {
            $message = '[' . $currentUser->email . ']' . $message;
        }

        $compositeMethod = 'add' . ucfirst($level);
        $this->channels[$stream]->{$compositeMethod}($message, $context);

        return true;
    }

    protected function registerNewStream($name)
    {
        $stream = new Logger($name);
        $stream->pushHandler(
            new StreamHandler($this->generateFilename($name))
        );
        $stream->pushHandler(
            new StreamHandler(
                $this->generateFilename($name, 'important'),
                $this->exceptionalLogLevel
            )
        );

        $stream->pushProcessor(new WebProcessor());
        $stream->pushProcessor(new GitProcessor());
        $stream->pushProcessor(new IntrospectionProcessor(Logger::INFO, ['Monolog\\', 'Giraffe\\Logging\\', 'Giraffe\\Authorization\\']));

        $this->channels[$name] = $stream;
    }

    protected function generateFilename($stream, $extra = null)
    {
        // get base class out of stream if it's a namespaced class
        $stream = class_basename($stream);

        if ($extra) {
            return storage_path() . $this->path . "/$stream-$extra-" . date('Y-m-d') . '.log';
        }
        return storage_path() . $this->path . "/$stream-" . date('Y-m-d') . '.log';
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