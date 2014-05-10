<?php

class FeedServiceTest extends TestCase
{

    const POST_REPOSITORY = 'Giraffe\Repositories\PostRepository';
    const TEST = 'Giraffe\Services\FeedService';
    const SHOUT_REPOSITORY = 'Giraffe\Repositories\ShoutRepository';
    const USER_REPOSITORY = 'Giraffe\Repositories\UserRepository';

    public function disarm()
    {
        $gatekeeper = App::make('Giraffe\Helpers\Rights\Gatekeeper');
        $gatekeeper->disarm();
    }

    /**
     * @test
     */
    public function it_can_create_a_shout_post()
    {
        $this->disarm();

        $userRepository = Mockery::mock(self::USER_REPOSITORY);
        $userRepository->shouldReceive('get')
            ->with('1')
            ->andReturn(json_decode("{'id': 1}"));
        App::instance(self::USER_REPOSITORY, $userRepository);

        $shoutRepository = Mockery::mock(self::SHOUT_REPOSITORY);
        $shoutRepository->shouldReceive('create')
            ->with(['user_id' => 1, 'body' => 'Lorem ipsum', 'html_body' => '<p>Lorem ipsum</p>'])
            ->andReturn(json_decode('{"id": 1, "country": "United States"}'));
        App::instance(self::SHOUT_REPOSITORY, $shoutRepository);

        $postRepository = Mockery::mock(self::POST_REPOSITORY);
        $postRepository->shouldReceive('createWithPostable')
            ->with(['user_id' => 1], Mockery::any())
            ->andReturn(json_decode('{"id": 2, "postable": {"body": "Lorem ipsum"}}'));
        $postRepository->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn(json_decode('{"id": 2, "postable": {"body": "Lorem ipsum"}}'));
        App::instance(self::POST_REPOSITORY, $postRepository);

        $feed = App::make(self::TEST);
        $post = $feed->createPost('1', 'Lorem ipsum', []);
        $postModel = $feed->getPost($post);

        // assert post properly saved
        $this->assertEquals($postModel->postable->body, 'Lorem ipsum');
        // assert post takes user location if not explicitly set
        $this->assertEquals($postModel->country, 'United States');
    }
}