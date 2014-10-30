<?php

class StickyTest extends AcceptanceCase
{

    /**
     * @test
     */
    public function clients_see_no_stickies_by_default()
    {
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->stickies));
    }

    /**
     * @test
     */
    public function stickies_can_be_set_from_the_cli()
    {
        Artisan::call('lg:sticky:post', ['body' => 'Test sticky']);
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->stickies));
        $this->assertEquals('Test sticky', $fetch->stickies[0]->body);
    }

    /**
     * @test
     */
    public function there_can_only_be_one_sticky_at_a_time()
    {
        Artisan::call('lg:sticky:post', ['body' => 'Test sticky']);
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->stickies));
        $this->assertEquals('Test sticky', $fetch->stickies[0]->body);
        Artisan::call('lg:sticky:post', ['body' => 'New sticky']);
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(1, count($fetch->stickies));
        $this->assertEquals('New sticky', $fetch->stickies[0]->body);

    }

    /**
     * @test
     */
    public function stickies_can_be_dismissed_from_the_cli()
    {
        Artisan::call('lg:sticky:post', ['body' => 'Test sticky']);
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();

        Artisan::call('lg:sticky:clear');
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(0, count($fetch->stickies));

    }

    /**
     * @test
     */
    public function stickies_can_contain_html_and_markdown()
    {
        Artisan::call(
               'lg:sticky:post',
               ['body' => 'Test **bold** sticky. With a <a href="http://google.com">link</a>.']
        );
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals(
             '<p>Test <strong>bold</strong> sticky. With a <a href="http://google.com" target="_blank">link</a>.</p>',
             $fetch->stickies[0]->html_body
        );
    }

    /**
     * @test
     */
    public function stickies_can_be_given_a_class()
    {
        Artisan::call(
            'lg:sticky:post',
            ['body' => 'Sticky body', 'class' => 'alert']
        );
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals('<p>Sticky body</p>',$fetch->stickies[0]->html_body);
        $this->assertEquals('alert', $fetch->stickies[0]->class);
    }

    /**
     * @test
     */
    public function stickies_have_class_general_by_default()
    {
        Artisan::call(
            'lg:sticky:post',
            ['body' => 'Sticky body']
        );
        $fetch = $this->callJson('GET', '/api/stickies');
        $this->assertResponseOk();
        $this->assertEquals('<p>Sticky body</p>',$fetch->stickies[0]->html_body);
        $this->assertEquals('general', $fetch->stickies[0]->class);
    }

} 