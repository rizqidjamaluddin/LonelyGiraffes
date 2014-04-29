<?php namespace Giraffe;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Giraffe\Helpers\Geolocation\GeolocationProviderInterface', 'Giraffe\Helpers\Geolocation\GeonameGeolocationProvider');
    }

}