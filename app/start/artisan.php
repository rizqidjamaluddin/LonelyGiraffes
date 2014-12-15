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

Artisan::add(new RunSocketServer());

// lgdb
Artisan::add(new GenerateGeonames());
Artisan::add(new ImportGeonamesZipData());

// lgutil
Artisan::add(new Promote());
Artisan::add(new GeonameFilter());
Artisan::add(new Notify());

// lgsetup
Artisan::add(new SeedOAuth());
Artisan::add(new SeedLookup());

// lgdev
Artisan::add(new RunTests());
Artisan::add(new EmulateFail());

// lg:migrate
Artisan::add(new MigrateUsers());

// stickies
Artisan::add(new PostSticky());
Artisan::add(new ClearSticky());
