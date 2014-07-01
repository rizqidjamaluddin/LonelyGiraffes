<?php namespace Giraffe;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\NearbySearchStrategies\FiveDegreeCellStrategy\FiveDegreeCellSearchStrategy;
use Giraffe\Geolocation\Providers\GeonameLocationProvider;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Giraffe\Helpers\Geolocation\GeolocationProvider', 'Giraffe\Helpers\Geolocation\GeonameGeolocationProvider');
        $this->app->singleton('Giraffe\Authorization\Gatekeeper');
        $this->app->singleton('Giraffe\Authorization\GatekeeperProvider', 'Giraffe\Authorization\GiraffeGatekeeperProvider');

        $this->app->singleton('Giraffe\Parser\ParserDriver', 'Giraffe\Parser\ParsedownPurifierParserDriver');
        $this->app->singleton('Giraffe\Logging\Log');
        $this->app->singleton('Giraffe\Geolocation\LocationService');
        $this->app->singleton('Giraffe\Geolocation\Providers\GeonameLocationProvider');

        $this->app->singleton('Giraffe\Users\UserService');
    }

    public function boot()
    {
        $this->app->make('Giraffe\Logging\Log')->boot();

        /** @var LocationService $locationService */
        $locationService = $this->app->make('Giraffe\Geolocation\LocationService');

        $geonameLocationProvider = $this->app->make('Giraffe\Geolocation\Providers\GeonameLocationProvider');
        $locationService->pushProvider($geonameLocationProvider);
        $locationService->setCanonicalProvider($geonameLocationProvider);

        $fiveDegreeCellStrat = new FiveDegreeCellSearchStrategy();
        $locationService->setDefaultNearbySearchStrategy($fiveDegreeCellStrat);
    }

}