<?php
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\AwsS3;

return [
    /*
     * Path where files will be stored before they are sent up to the permanent
     * storage location. Attached to the end of /app/path.
     */
    'staging_path' => 'images',

    /**
     * Permanent storage medium for avatars. Return a League\Flysystem\Filesystem instance.
     * https://github.com/thephpleague/flysystem
     */
    'medium' => function () {
        $accessKey = 'AKIAI4HJVQ3DFNICQVAA';
        $secretKey = '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';
        $bucket = 'lonelygiraffes.com';

        $client = S3Client::factory(['key' => $accessKey,'secret' => $secretKey]);
        $filesystem = new Filesystem(new AwsS3($client, $bucket, 'production'));
        return $filesystem;
    },

    /**
     * Prefix to use when serving image URLs to clients.
     */
    'url_prefix'   => 'http://lonelygiraffes.com.s3-website-us-west-2.amazonaws.com'
];