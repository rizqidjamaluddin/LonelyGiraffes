<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

// lgdb
Artisan::add(new GenerateGeonames());

// lgutil
Artisan::add(new Promote());
Artisan::add(new GeonameFilter());

// lgsetup
Artisan::add(new SeedOAuth());
Artisan::add(new SeedLookup());

// lgdev
Artisan::add(new RunTests());