<?php namespace Giraffe;
use Giraffe\Common\EventRelay;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchStrategy;
use Giraffe\Geolocation\Providers\GeonameLocationProvider;
use Giraffe\Geolocation\Providers\GeonamePostalCodeLocationProvider;
use Giraffe\Notifications\Registry\NotifiableRegistry;
use Giraffe\Notifications\NotificationRegistry;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotification\SystemNotification;
use Giraffe\Notifications\SystemNotification\SystemNotificationRepository;
use Giraffe\Sockets\Pipeline;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('Giraffe\Geolocation\LocationProvider', 'Giraffe\Geolocation\Providers\GeonameLocationProvider');
        $this->app->singleton('Giraffe\Authorization\Gatekeeper');
        $this->app->singleton('Giraffe\Authorization\GatekeeperProvider', 'Giraffe\Authorization\GiraffeGatekeeperProvider');

        $this->app->singleton('Giraffe\Parser\ParserDriver', 'Giraffe\Parser\ParsedownPurifierParserDriver');
        $this->app->singleton('Giraffe\Logging\Log');
        $this->app->singleton('Giraffe\Geolocation\LocationService');
        $this->app->singleton('Giraffe\Geolocation\Providers\GeonameLocationProvider');

        $this->app->singleton('Giraffe\Users\UserService');

        $this->app->singleton(Pipeline::class);
        $this->app->singleton(EventRelay::class);
    }

    public function boot()
    {
        $this->app->make('Giraffe\Logging\Log')->boot();

        /*
         * Geolocation
         */

        /** @var LocationService $locationService */
        $locationService = $this->app->make('Giraffe\Geolocation\LocationService');

        $geonameLocationProvider = $this->app->make(GeonameLocationProvider::class);
        $geonamePostalCodeLocationProvider = $this->app->make(GeonamePostalCodeLocationProvider::class);
        $locationService->pushProvider($geonameLocationProvider);
        $locationService->pushProvider($geonamePostalCodeLocationProvider);
        $locationService->setCanonicalProvider($geonameLocationProvider);

        $fiveDegreeCellStrat = new TwoDegreeCellSearchStrategy();
        $locationService->setDefaultNearbySearchStrategy($fiveDegreeCellStrat);
    }

}