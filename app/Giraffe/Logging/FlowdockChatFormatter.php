<?php  namespace Giraffe\Logging; 
use Monolog\Formatter\FormatterInterface;

class FlowdockChatFormatter implements FormatterInterface
{

    /**
     * @var
     */
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function format(array $record)
    {
        $tags = array(
            '#logs',
            '#' . strtolower($record['level_name']),
            '#' . $record['channel'],
        );

        foreach ($record['extra'] as $value) {
            $tags[] = '#' . $value;
        }

        $record['flowdockChat'] = array(
            'content' => '',
            'external_user_name' => $record['channel'],
            'tags' => ''
        );

        return $record;
    }

    /**
     * Formats a set of log records.
     *
     * @param  array $records A set of records to format
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $formatted = array();
        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }
        return $formatted;
    }
}