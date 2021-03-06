<?php

use Giraffe\Authorization\Gatekeeper;
use Faker\Factory as Faker;
use Faker\Provider\Image as FakerImage;
use Giraffe\Images\ImageTypeModel;
use Giraffe\Users\UserModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageTest extends AcceptanceCase
{

    /**
     * @test
     */
    public function goddammit_php_unit()
    {
        $this->assertTrue(true);
    }

    /**
     *
     */
    protected function it_can_create_and_delete_images()
    {
        // Setup
        $mario = $this->registerMario();
        $this->asUser($mario->hash);

        /////// Create ///////
        $img = FakerImage::image('/tmp', 900, 900);

        $file = new UploadedFile (
            $img,
            "image.jpg",
            "image/jpeg",
            File::size($img)
        );

        // Create the image & check that the response is correct
        $response = $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
        $this->assertResponseStatus(200);
        $image = $response->images[0];
        $image_location = 'images/'.$mario->hash."/".$image->hash.".".$image->extension;
        $this->assertEquals(url($image_location), $image->href);
        $thumb_location = 'images/'.$mario->hash."/".$image->hash."_thumb.".$image->extension;
        $this->assertEquals(url($thumb_location), $image->href_thumb);


        // Check that the image physically exists on disk
        $this->assertTrue(File::exists(public_path()."/".$image_location));

        // Check that it is correctly associated with the user (Mario)
        $getUser = $this->toJson($this->call("GET", "/api/users/" . $mario->hash));
        $this->assertResponseStatus(200);
        $this->assertEquals('Mario', $getUser->users[0]->name);
        $this->assertEquals('M', $getUser->users[0]->gender);
        $this->assertEquals(url($image_location), $getUser->users[0]->pic->href);


        /////// Delete ///////
        $response = $this->callJson('DELETE', '/api/images/' . $image->hash);
        $this->assertResponseStatus(200);
        $this->assertFalse(File::exists(public_path()."/".$image_location));


        $this->cleanup($mario->hash);
    }

    /**
     * @depends it_can_create_and_delete_images
     */
    protected function it_cannot_create_forbidden_images()
    {
        // Setup
        $mario = $this->registerMario();
        $this->asUser($mario->hash);

        /////// Test for non-image ///////
        $dumb_file = FakerImage::image('/tmp', '/tmp');
        $file = new UploadedFile (
            $dumb_file,
            "fakefile.txt",
            "audio/mpeg3",
            File::size($dumb_file)
        );
        $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
        $this->assertResponseStatus(422);


//        /////// Test for too large ///////
//        do {
//            $img = FakerImage::image('/tmp', 1920, 1080);
//        } while(File::size($img) <= 200000);
//        $file = new UploadedFile (
//            $img,
//            "image.jpg",
//            "image/jpeg",
//            File::size($img)
//        );
//        $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
//        $this->assertResponseStatus(422);
    }

    /**
     *
     * @depends it_can_create_and_delete_images
     */
    protected function it_can_overwrite_profile_images()
    {
        // Setup
        $mario = $this->registerMario();
        $this->asUser($mario->hash);

        /////// Create 1 ///////
        $img = FakerImage::image('/tmp', 900, 900);

        $file = new UploadedFile (
            $img,
            "image.jpg",
            "image/jpeg",
            File::size($img)
        );

        $response = $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
        $this->assertResponseStatus(200);
        $image1 = $response->images[0];
        $image1_location = 'images/'.$mario->hash."/".$image1->hash.".".$image1->extension;
        $this->assertEquals(url($image1_location), $image1->href);
        $thumb1_location = 'images/'.$mario->hash."/".$image1->hash."_thumb.".$image1->extension;
        $this->assertEquals(url($thumb1_location), $image1->href_thumb);

        $this->assertTrue(File::exists(public_path()."/".$image1_location));


        /////// Create 2 ///////
        $img = FakerImage::image('/tmp', 900, 900);

        $file = new UploadedFile (
            $img,
            "image.jpg",
            "image/jpeg",
            File::size($img)
        );

        $response = $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
        $this->assertResponseStatus(200);
        $image2 = $response->images[0];
        $image2_location = 'images/'.$mario->hash."/".$image2->hash.".".$image2->extension;
        $this->assertEquals(url($image2_location), $image2->href);
        $thumb2_location = 'images/'.$mario->hash."/".$image2->hash."_thumb.".$image2->extension;
        $this->assertEquals(url($thumb2_location), $image2->href_thumb);

        $this->assertTrue(File::exists(public_path()."/".$image2_location));


        /////// Check that 1 is now gone ///////
        $this->assertFalse(File::exists(public_path()."/".$image1_location));


        /////// Delete 2 ///////
        $response = $this->callJson('DELETE', '/api/images/' . $image2->hash);
        $this->assertResponseStatus(200);
        $this->assertFalse(File::exists(public_path()."/".$image2_location));


        $this->cleanup($mario->hash);
    }

    /**
     *
     */
    protected function it_cannot_alter_images_of_another_user()
    {
        /////// Create ///////
        $mario = $this->registerMario();
        $this->asUser($mario->hash);

        $img = FakerImage::image('/tmp', 900, 900);
        $file = new UploadedFile (
            $img,
            "image.jpg",
            "image/jpeg",
            File::size($img)
        );

        $response = $this->callJson('POST', '/api/images', array('type' => 'profile_pic'), array('image' => $file));
        $this->assertResponseStatus(200);
        $image = $response->images[0];
        $image_location = 'images/'.$mario->hash."/".$image->hash.".".$image->extension;
        $this->assertEquals(url($image_location), $image->href);
        $thumb_location = 'images/'.$mario->hash."/".$image->hash."_thumb.".$image->extension;
        $this->assertEquals(url($thumb_location), $image->href_thumb);

        $this->assertTrue(File::exists(public_path()."/".$image_location));


        /////// Delete (should fail) ///////
        $bowser = $this->registerBowser();
        $this->asUser($bowser->hash);
        $response = $this->callJson('DELETE', '/api/images/' . $image->hash);
        $this->assertResponseStatus(403);
        $this->assertTrue(File::exists(public_path()."/".$image_location));

        $this->cleanup($mario->hash);
    }

    private function cleanup($hash) {
        File::deleteDirectory(public_path()."/images/".$hash);
    }
}