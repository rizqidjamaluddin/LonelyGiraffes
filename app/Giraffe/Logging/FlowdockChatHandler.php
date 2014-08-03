<?php  namespace Giraffe\Logging; 
use Monolog\Handler\FlowdockHandler;
use Monolog\Handler\MissingExtensionException;

class FlowdockChatHandler extends FlowdockHandler
{

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * {@inheritdoc}
     *
     * @param  array  $record
     * @return string
     */
    protected function generateDataStream($record)
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

    private function buildContent($record)
    {
        return json_encode($record['formatted']['flowdockChat']);
    }

    private function buildHeader($content)
    {
        $header = "POST /v1/messages/chat/" . $this->apiToken . " HTTP/1.1\r\n";
        $header .= "Host: api.flowdock.com\r\n";
        $header .= "Content-Type: application/json\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }
} 