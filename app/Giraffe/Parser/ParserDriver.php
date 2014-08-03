<?php  namespace Giraffe\Parser;

interface ParserDriver
{
    public function parseLinks($input);

    /**
     * Parse content for "rich" use, such as posts and events.
     *
     * Allow: images, headers, quotes, lists, everything allowed in parseComment.
     *
     * @param string $input
     * @return mixed
     */
    public function parseRich($input);

    /**
     * Parse content for "comment" use, such as user comments and messages.
     *
     * Allow: paragraph, strong, emphasis, anchors.
     *
     * @param $input
     * @return mixed
     */
    public function parseComment($input);

    /**
     * Parse full markdown without securing any content. Meant for safe use only.
     *
     * @param string $input
     * @return mixed
     */
    public function parseTrusted($input);
} 