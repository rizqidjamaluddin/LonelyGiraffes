<?php namespace Giraffe;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Giraffe\Helpers\Geolocation\GeolocationProvider', 'Giraffe\Helpers\Geolocation\GeonameGeolocationProvider');
        $this->app->singleton('Giraffe\Authorization\Gatekeeper');
        $this->app->singleton('Giraffe\Authorization\GatekeeperProvider', 'Giraffe\Authorization\GiraffeGatekeeperProvider');

        $this->app->singleton('Giraffe\Parser\ParserDriver', 'Giraffe\Parser\ParsedownPurifierParserDriver');
        $this->app->singleton('Giraffe\Logging\Log');
    }

    public function boot()
    {
        $this->app->make('Giraffe\Logging\Log')->boot();
    }

}