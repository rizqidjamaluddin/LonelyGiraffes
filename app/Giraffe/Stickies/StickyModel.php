<?php  namespace Giraffe\Stickies;

use Giraffe\Parser\Parser;
use Giraffe\Support\Transformer\Transformable;
use Giraffe\Support\Transformer\Transformer;

/**
 * @property string $body
 * @property string $html_body
 * @property string $class
 */
class StickyModel extends \Eloquent implements Transformable
{
    protected $table = 'stickies';
    protected $fillable = ['body', 'html_body', 'class'];

    /**
     * @param        $body
     * @param string $class
     * @return static
     */
    public static function post($body, $class = '')
    {
        $i = new static;
        $i->body = $body;

        /** @var Parser $parser */
        $parser = \App::make(Parser::class);

        $i->html_body = $parser->parseTrusted($body);
        $i->class = $class;
        return $i;
    }

}