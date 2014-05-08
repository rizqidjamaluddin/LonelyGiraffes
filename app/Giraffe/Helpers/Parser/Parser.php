<?php  namespace Giraffe\Helpers\Parser;

class Parser
{
    /**
     * @var ParserDriver
     */
    private $parserDriver;

    public function __construct(ParserDriver $parserDriver)
    {
        $this->parserDriver = $parserDriver;
    }

    public function parse($input)
    {
        return $this->parserDriver;
    }
} 