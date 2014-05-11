<?php namespace Giraffe;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Giraffe\Helpers\Geolocation\GeolocationProvider', 'Giraffe\Helpers\Geolocation\GeonameGeolocationProvider');
        $this->app->singleton('Giraffe\Helpers\Rights\Gatekeeper');
        $this->app->singleton('Giraffe\Helpers\Rights\GatekeeperProvider', 'Giraffe\Helpers\Rights\GiraffeGatekeeperProvider');


        $this->app->singleton('Giraffe\Helpers\Parser\ParserDriver', 'Giraffe\Helpers\Parser\ParsedownPurifierParserDriver');
        $this->app->singleton('Giraffe\Helpers\Logger\Log');
    }

    public function boot()
    {
        $this->app->make('Giraffe\Helpers\Logger\Log')->boot();
    }

}