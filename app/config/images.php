<?php
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
    'medium' => function() {

    },

    /**
     * Prefix to use when serving image URLs to clients.
     */
    'url_prefix' => 'http://i.lonelygiraffes.com/'


];