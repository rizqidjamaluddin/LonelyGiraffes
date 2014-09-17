<?php  namespace Giraffe\Stickies;

use Giraffe\Parser\Parser;

/**
 * @property string $body
 * @property string $html_body
 */
class StickyModel extends \Eloquent
{
    protected $table = 'stickies';
    protected $fillable = ['body', 'html_body'];

    /**
     * @param $body
     * @return static
     */
    public static function post($body)
    {
        $i = new static;
        $i->body = $body;

        /** @var Parser $parser */
        $parser = \App::make(Parser::class);

        $i->html_body = $parser->parseTrusted($body);
        return $i;
    }
} 