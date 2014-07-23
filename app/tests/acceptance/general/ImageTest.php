<?php

use Giraffe\Authorization\Gatekeeper;
use Faker\Factory as Faker;
use Faker\Provider\Image as FakerImage;
use Giraffe\Images\ImageTypeModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageTest extends AcceptanceCase
{

    /**
     * @test
     */
    public function it_can_create_and_delete_images()
    {
        // Setup
        $this->create_profile_pic();
        $mario = $this->registerMario();
        $this->asUser($mario->hash);

        /////// Create ///////
        $img = FakerImage::image('/tmp', 900, 900);

        $file = new UploadedFile (
            $img,
            "image.jpg",
            "image/jpeg"
        );

        $response = $this->callJson('POST', '/api/images', array('type' => 'profile pic'), array('image' => $file));
        $this->assertResponseStatus(200);
        $image = $response->images[0];
        $image_location = 'images/'.$mario->hash."/".$image->hash.".".$image->extension;
        $this->assertEquals(url($image_location), $image->href);
        $thumb_location = 'images/'.$mario->hash."/".$image->hash."_thumb.".$image->extension;
        $this->assertEquals(url($thumb_location), $image->href_thumb);

        $this->assertTrue(File::exists(public_path()."/".$image_location));


        /////// Delete ///////
        $response = $this->callJson('DELETE', '/api/images/' . $image->hash);
        $this->assertResponseStatus(200);
        $this->assertFalse(File::exists(public_path()."/".$image_location));
    }

    /**
     * @test
     * @depends it_can_create_and_delete_images
     */
    public function it_cannot_create_forbidden_images()
    {
    }

    /**
     * @test
     * @depends it_can_create_and_delete_images
     */
    public function it_can_overwrite_profile_images()
    {
    }

    /**
     * @test
     */
    public function it_cannot_alter_images_of_another_user()
    {
    }

    private function create_profile_pic() {
        ImageTypeModel::create(array(
            'name' => 'profile pic',
            'unique_per_user' => false
        ));
    }
}