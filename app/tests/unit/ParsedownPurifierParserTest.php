<?php

class ParsedownPurifierParserTest extends TestCase
{
    const TEST = 'Giraffe\Parser\ParsedownPurifierParserDriver';

    /**
     * @test
     */
    public function it_always_processes_an_empty_string_verbatim()
    {
        $parser = App::make(self::TEST);
        $raw = '';
        $expected = '';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseTrusted($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_can_process_basic_markdown()
    {
        $parser = App::make(self::TEST);
        $raw = 'Hello, this string has a **bold** element, and an *italic* element. And a ***both bold and italic*** part.';
        $expected = '<p>Hello, this string has a <strong>bold</strong> element, and an <em>italic</em> element.' .
            ' And a <strong><em>both bold and italic</em></strong> part.</p>';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseTrusted($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_can_make_links()
    {
        $parser = App::make(self::TEST);
        $raw = 'This is a [link test](http://google.com).';
        $expected = '<p>This is a <a href="http://google.com" target="_blank">link test</a>.</p>';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);


        $parsed = $parser->parseTrusted($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_can_handle_mixed_html_and_markdown()
    {
        $parser = App::make(self::TEST);
        $raw = "Some **markdown bold** with some <strong>HTML bold</strong>.\n\nSecond paragraph; &amp; and & should be fixed!";
        $expected = '<p>Some <strong>markdown bold</strong> with some <strong>HTML bold</strong>.</p>'.
            "\n<p>Second paragraph; &amp; and &amp; should be fixed!</p>";

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseTrusted($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_can_handle_unescpaed_entities()
    {
        $parser = App::make(self::TEST);
        $raw = '**Bold** text, a mismatched <3 sitting in the middle, and <strong>a classic HTML bold</strong>.';
        $expected = '<p><strong>Bold</strong> text, a mismatched &lt;3 sitting in the middle, and <strong>a classic HTML bold</strong>.</p>';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);

        // trusted parse is not tested, because behavior of invalid HTML is undefined. Trusted data is assumed
        // to be clean.
    }

    /**
     * @test
     */
    public function it_splits_multiline_text_into_paragraphs_except_single_line_breaks()
    {
        $parser = App::make(self::TEST);
        $raw = "This is a line.\n\n\nThis is another line.\nThis line should be in the same paragraph as before.\n\nThis is the last line.";
        $expected = "<p>This is a line.</p>\n<p>This is another line.\nThis line should be in the same paragraph as before.</p>\n" .
            "<p>This is the last line.</p>";

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseTrusted($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_prevents_script_tags_in_rich_and_comment_modes()
    {
        $parser = App::make(self::TEST);
        $raw = 'This is some text. <script>alert("foo");</script>Here is more text.';
        $expected = '<p>This is some text. Here is more text.</p>';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * @test
     */
    public function it_prevents_scripts_as_attributes_in_rich_and_comment_modes()
    {
        $parser = App::make(self::TEST);
        $raw = 'This <a href="#" onclick="alert(\'foo\')">link</a> is dangerous!';
        $expected = '<p>This <a href="#">link</a> is dangerous!</p>';

        $parsed = $parser->parseRich($raw);
        $this->assertEquals($parsed, $expected);

        $parsed = $parser->parseComment($raw);
        $this->assertEquals($parsed, $expected);
    }
}