<?php  namespace Giraffe\Parser;

use Giraffe\Parser\ParserDriver;
use Mews\Purifier\Purifier;
use Parsedown;

class ParsedownPurifierParserDriver implements ParserDriver
{

    protected $trusted_settings = [
        'AutoFormat.Linkify' => true,
        'HTML.TargetBlank' => true,
    ];

    protected  $purify_rich_settings = [
        'HTML.Allowed' => 'p,strong,em,a[href],img[src|alt],h1,h2,h3,blockquote,q,ul,cite,ol,li',
        'AutoFormat.Linkify' => true,
        'HTML.TargetBlank' => true,
    ];


    protected  $purify_comment_settings = [
        'HTML.Allowed' => 'p,strong,em,a[href]',
        'AutoFormat.Linkify' => true,
        'HTML.TargetBlank' => true,
    ];

    protected $purify_linkify_settings = [
        'HTML.Allowed' => 'p,a[href]',
        'AutoFormat.Linkify' => true,
        'HTML.TargetBlank' => true,
    ];

    /**
     * @var Parsedown
     */
    private $parsedown;
    /**
     * @var \Mews\Purifier\Purifier
     */
    private $purifier;

    public function __construct(Parsedown $parsedown, Purifier $purifier)
    {
        $this->parsedown = $parsedown;
        $this->purifier = $purifier;
    }

    public function parseRich($input)
    {
        $input = $this->parsedown->text($input);
        $input = $this->purifier->clean($input, $this->purify_rich_settings);
        return $input;
    }

    public function parseComment($input)
    {
        $input = $this->parsedown->text($input);
        $input = $this->purifier->clean($input, $this->purify_comment_settings);
        return $input;
    }

    public function parseTrusted($input)
    {
        $input = $this->parsedown->text($input);
        $input = $this->purifier->clean($input, $this->trusted_settings);
        return $input;
    }

    public function parseLinks($input)
    {
        $input = $this->purifier->clean($input, $this->purify_linkify_settings);
        return $input;
    }
}