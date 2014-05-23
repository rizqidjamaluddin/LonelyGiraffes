<?php  namespace Giraffe\Logging;

use Illuminate\Log\Writer;
use Monolog\Logger;

class Log
{
    /**
     * @var Logger
     */
    protected $monolog;
    /**
     * @var Writer
     */
    private $writer;

    public function __construct(Writer $writer)
    {
        $this->writer = $writer;
    }

    public function boot()
    {
        $this->writer->useDailyFiles(storage_path() . '/logs/lg.log');
        $this->monolog = $this->writer->getMonolog();
        // any monolog-specific things can be done here.
    }

    /*
     * -- Log levels --
     */

    public function debug($message, $context)
    {
        $this->monolog->addDebug($message, $context);
    }

    public function info($message, $context)
    {
        $this->monolog->addInfo($message, $context);
    }

    public function notice($message, $context)
    {
        $this->monolog->addNotice($message, $context);
    }

    public function warning($message, $context)
    {
        $this->monolog->addWarning($message, $context);
    }

    public function error($message, $context)
    {
        $this->monolog->addError($message, $context);
    }

    public function critical($message, $context)
    {
        $this->monolog->addCritical($message, $context);
    }

    public function alert($message, $context)
    {
        $this->monolog->addAlert($message, $context);
    }
} 