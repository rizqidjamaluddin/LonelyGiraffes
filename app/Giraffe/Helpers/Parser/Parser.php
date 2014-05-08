<?php  namespace Giraffe\Helpers\Parser;

class Parser
{

    /**
     * Simply return HTML-safe versions if not enabled.
     */
    const ENABLED = true;

    /**
     * @var ParserDriver
     */
    private $parserDriver;

    public function __construct(ParserDriver $parserDriver)
    {
        $this->parserDriver = $parserDriver;
    }

    public function parseRich($input)
    {
        if (!self::ENABLED) return e($input);
        return $this->parserDriver->parseRich($input);
    }

    public function parseComment($input)
    {
        if (!self::ENABLED) return e($input);
        return $this->parserDriver->parseComment($input);
    }

    public function parseTrusted($input)
    {
        if (!self::ENABLED) return e($input);
        return $this->parserDriver->parseTrusted($input);
    }
} 