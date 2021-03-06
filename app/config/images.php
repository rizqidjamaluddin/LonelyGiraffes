<?php
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\AwsS3;

return [

    /**
     * Max size (in bytes) of user avatar uploads.
     */
    'max-size' => 10000000,

    /*
     * Path where files will be stored before they are sent up to the permanent
     * storage location. Attached to the end of /app/path.
     */
    'staging' => function(){
        return new Filesystem(new \League\Flysystem\Adapter\Local(storage_path() . '/image-staging'));
    },

    /**
     * Permanent storage medium for avatars. Return a League\Flysystem\Filesystem instance.
     * https://github.com/thephpleague/flysystem
     */
    'medium' => function () {
        $accessKey = 'AKIAI4HJVQ3DFNICQVAA';
        $secretKey = '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';
        $bucket = 'lonelygiraffes.com';

        $client = S3Client::factory(['key' => $accessKey,'secret' => $secretKey,'region' => 'us-west-2']);
        $filesystem = new Filesystem(new AwsS3($client, $bucket, 'production'));
        return $filesystem;
    },

    /**
     * Prefix to use when serving image URLs to clients.
     */
    'url_prefix'   => 'http://s3-us-west-2.amazonaws.com/lonelygiraffes.com/production'
];
