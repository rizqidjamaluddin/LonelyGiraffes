<?php

class GeolocationTest extends AcceptanceCase
{

    public function setUp()
    {
        parent::setUp();
        $m = microtime(true);
        Artisan::call('lgdb:geonames', ['source' => 'app/data/geonames-1M-testdata.txt']);
    }

    /**
     * We use an aggregate to prevent phpunit from needing to-reseed the whole dataset every time.
     *
     * @test
     */
    public function aggregate()
    {
        $this->it_cannot_return_results_for_one_letter_hints();
        $this->it_can_look_up_a_city_name_and_state_name();
        $this->it_has_a_humanized_string_form();
        $this->it_can_distinguish_cities_of_the_same_name_in_one_country();

        $this->it_can_find_unicode_city_names();
        $this->it_shows_a_bad_request_without_a_query_string();
    }


    protected function it_can_look_up_a_city_name_and_state_name()
    {
        $results = $this->toJson($this->call('GET', '/api/locations?hint=new'))->locations;
        $this->assertResponseOk();

        // NY should be the biggest population city with "new" in the name
        $expectNewYork = $results[0];
        $this->assertEquals($expectNewYork->city, 'New York City');
        $this->assertEquals($expectNewYork->state, 'New York');
        $this->assertEquals($expectNewYork->country, 'United States');

        // Sydney, New South Wales expected to be the second result
        $expectNSW = $results[1];
        $this->assertEquals($expectNSW->city, 'Sydney');
        $this->assertEquals($expectNSW->state, 'New South Wales');
        $this->assertEquals($expectNSW->country, 'Australia');
    }

    protected function it_has_a_humanized_string_form()
    {
        $results = $this->toJson($this->call('GET', '/api/locations?hint=new'))->locations;
        $this->assertResponseOk();

        // similar to above test, with humanized check
        $expectNewYork = $results[0];
        $this->assertEquals($expectNewYork->humanized, 'New York City, New York, United States');
        $this->assertEquals($expectNewYork->city, 'New York City');
        $this->assertEquals($expectNewYork->state, 'New York');
        $this->assertEquals($expectNewYork->country, 'United States');

    }

    protected function it_cannot_return_results_for_one_letter_hints()
    {
        $response = $this->toJson($this->call('GET', '/api/locations?hint=a'));
        $this->assertResponseStatus(400);
        $this->assertFalse(isset($response->locations));
    }

    protected function it_can_distinguish_cities_of_the_same_name_in_one_country()
    {
        // Apparently there are 2 places in Mexico called Gustavo A. Madero
        $results = $this->toJson($this->call('GET', '/api/locations?hint=gustavo'))->locations;
        $this->assertResponseOk();

        $expectTamaulipasCity = $results[1];
        $this->assertEquals($expectTamaulipasCity->city, 'Gustavo A. Madero');
        $this->assertEquals($expectTamaulipasCity->state, 'Tamaulipas');
        $this->assertEquals($expectTamaulipasCity->country, 'Mexico');
        $expectFederalCity = $results[0];
        $this->assertEquals($expectFederalCity->city, 'Gustavo A. Madero');
        $this->assertEquals($expectFederalCity->state, 'The Federal District');
        $this->assertEquals($expectFederalCity->country, 'Mexico');
    }

    protected function it_can_find_unicode_city_names()
    {
        $results = $this->toJson($this->call('GET', '/api/locations', ['hint' => 'Ghāziābād']))->locations;
        $this->assertResponseOk();
        $expectNewYork = $results[0];
        $this->assertEquals($expectNewYork->city, 'Ghāziābād');
        $this->assertEquals($expectNewYork->state, 'Uttar Pradesh');
        $this->assertEquals($expectNewYork->country, 'India');
    }

    protected function it_shows_a_bad_request_without_a_query_string()
    {
        $this->call('GET', '/api/locations');
        $this->assertResponseStatus(400);
    }

} 