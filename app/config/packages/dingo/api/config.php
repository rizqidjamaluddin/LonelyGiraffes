<?php

return [

	/*
	|--------------------------------------------------------------------------
	| API Vendor
	|--------------------------------------------------------------------------
	|
	| Your vendor is used in the "Accept" request header and will be used by
	| the consumers of your API. Typically this will be the name of your
	| application or website.
	|
	*/

	'vendor' => 'lg',

	/*
	|--------------------------------------------------------------------------
	| Default API Version
	|--------------------------------------------------------------------------
	|
	| When a request is made to the API and no version is specified then it
	| will default to the version specified here.
	|
	*/

	'version' => 'v1',

	/*
	|--------------------------------------------------------------------------
	| Default API Prefix
	|--------------------------------------------------------------------------
	|
	| A default prefix to use for your API routes so you don't have to
	| specify it for each group.
	|
	*/

	'prefix' => null,

	/*
	|--------------------------------------------------------------------------
	| Default API Domain
	|--------------------------------------------------------------------------
	|
	| A default domain to use for your API routes so you don't have to
	| specify it for each group.
	|
	*/

	'domain' => null,

	/*
	|--------------------------------------------------------------------------
	| Authentication Providers
	|--------------------------------------------------------------------------
	|
	| The authentication providers that should be used when attempting to
	| authenticate an incoming API request.
	|
	*/

	'auth' => [
		'basic' => function($app)
		{
			return new Dingo\Api\Auth\BasicProvider($app['auth']);
		}
	],

	/*
	|--------------------------------------------------------------------------
	| Rate Limiting
	|--------------------------------------------------------------------------
	|
	| Consumers of your API can be limited to the amount of requests they can
	| make. You can configure the limit based on whether the consumer is
	| authenticated or unauthenticated.
	|
	| The "limit" is the number of requests the consumer can make within a
	| certain amount time which is defined by "reset" in minutes.
	|
	| By default rate limiting is disabled.
	|
	*/

	'rate_limiting' => [

		'authenticated' => [
			'limit' => 0,
			'reset' => 60
		],

		'unauthenticated' => [
			'limit' => 0,
			'reset' => 60
		],

		'exceeded' => 'API rate limit has been exceeded.'

	],

	/*
	|--------------------------------------------------------------------------
	| Response Formats
	|--------------------------------------------------------------------------
	|
	| Responses can be returned in multiple formats by registering different
	| response formatters. You can also customize an existing response
	| formatter.
	|
	*/

	'formats' => [

		'json' => new Dingo\Api\Http\ResponseFormat\JsonResponseFormat
		
	],


	/*
	|--------------------------------------------------------------------------
	| Transformer Embeds
	|--------------------------------------------------------------------------
	|
	| Transformers embed or relationships. The "key" is the query string
	| identifier which will hold the requested embeds. The "separator"
	| is the character that will separate each embed.
	|
	*/

	'embeds' => [

		'key' => 'embeds',
		'separator' => ','

	]

];
