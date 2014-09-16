<?php

class UserProfileTest extends AcceptanceCase
{
    protected $simpleBio = 'Hey, this is my bio!';

    /**
     * @test
     */
    public function users_have_an_empty_profile_by_default_with_valid_keys_but_empty_values()
    {
        $mario = $this->registerAndLoginAsMario();
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/profile");
        $this->assertResponseOk();

        $this->assertEquals(count($fetch->profiles), 1);
        $this->assertEquals($fetch->profiles[0]->bio, '');
    }

    /**
     * @test
     */
    public function users_can_add_a_bio_and_anyone_can_see_them()
    {
        $mario = $this->registerAndLoginAsMario();
        $addBio = $this->setSimpleBio($mario);
        $this->assertResponseOk();

        $this->validateSimpleBio($mario);
        $luigi = $this->registerAndLoginAsLuigi();
        $this->validateSimpleBio($mario);
        $this->asGuest();
        $this->validateSimpleBio($mario);
    }

    /**
     * @test
     */
    public function user_bios_cannot_be_over_140_characters_long()
    {
        // put simple bio first
        $mario = $this->registerAndLoginAsMario();
        $this->setSimpleBio($mario);

        $invalid = $this->callJson('PUT', "/api/users/{$mario->hash}/profile", ['bio' => str_repeat('x', 200)]);
        $this->assertResponseStatus(422);

        $this->validateSimpleBio($mario);

    }

    /**
     * @test
     */
    public function other_users_cannot_change_a_user_bio()
    {
        $mario = $this->registerAndLoginAsMario();
        $simple = $this->setSimpleBio($mario);

        $bowser = $this->registerAndLoginAsBowser();
        $invalid =  $this->callJson('PUT', "/api/users/{$mario->hash}/profile", ['bio' => 'Evil bio!']);
        $this->assertResponseStatus(403);

        // last check
        $this->validateSimpleBio($mario);
    }

    /**
     * @test
     */
    public function users_can_post_links_in_their_profile()
    {
        $mario = $this->registerAndLoginAsMario();
        $set = $this->callJson('PUT', "/api/users/{$mario->hash}/profile", ['bio' => "A URL: http://www.google.com"]);

        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/profile");
        $this->assertEquals("A URL: http://www.google.com", $fetch->profiles[0]->bio);
        $this->assertEquals(
            '<p>A URL: <a href="http://www.google.com" target="_blank">http://www.google.com</a></p>',
            $fetch->profiles[0]->html_bio
        );
    }

    /**
     * @param $mario
     */
    protected function validateSimpleBio($mario)
    {
        $fetch = $this->callJson('GET', "/api/users/{$mario->hash}/profile")->profiles[0];
        $this->assertResponseOk();
        $this->assertEquals($fetch->bio, $this->simpleBio);
    }

    /**
     * @param $mario
     * @return mixed
     */
    protected function setSimpleBio($mario)
    {
        return $this->callJson('PUT', "/api/users/{$mario->hash}/profile", ['bio' => $this->simpleBio]);
    }

} 