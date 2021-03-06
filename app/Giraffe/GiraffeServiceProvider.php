<?php namespace Giraffe;

use Config;
use Giraffe\Common\EventRelay;
use Giraffe\Geolocation\LocationService;
use Giraffe\Geolocation\NearbySearchStrategies\TwoDegreeCellStrategy\TwoDegreeCellSearchStrategy;
use Giraffe\Geolocation\Providers\GeonameLocationProvider;
use Giraffe\Geolocation\Providers\GeonamePostalCodeLocationProvider;
use Giraffe\Images\ImageService;
use Giraffe\Notifications\Registry\NotifiableRegistry;
use Giraffe\Notifications\NotificationRegistry;
use Giraffe\Notifications\NotificationService;
use Giraffe\Notifications\SystemNotification\SystemNotification;
use Giraffe\Notifications\SystemNotification\SystemNotificationRepository;
use Giraffe\Passwords\PasswordResetService;
use Giraffe\Sockets\Pipeline;
use Illuminate\Support\ServiceProvider;

class GiraffeServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(
            'Giraffe\Geolocation\LocationProvider',
            'Giraffe\Geolocation\Providers\GeonameLocationProvider'
        );
        $this->app->singleton('Giraffe\Authorization\Gatekeeper');
        $this->app->singleton(
            'Giraffe\Authorization\GatekeeperProvider',
            'Giraffe\Authorization\GiraffeGatekeeperProvider'
        );

        $this->app->singleton(ImageService::class);
        $this->app[ImageService::class]->setMaxSizeLimit(Config::get('images.max-size', 5000000));

        $this->app->singleton('Giraffe\Parser\ParserDriver', 'Giraffe\Parser\ParsedownPurifierParserDriver');
        $this->app->singleton('Giraffe\Logging\Log');
        $this->app->singleton('Giraffe\Geolocation\LocationService');
        $this->app->singleton('Giraffe\Geolocation\Providers\GeonameLocationProvider');
        $this->app->singleton(Mailer\Mailer::class);

        $this->app->singleton(PasswordResetService::class);
        $this->app->singleton('Giraffe\Users\UserService');

        $this->app->singleton(Pipeline::class);
        $this->app[Pipeline::class]->setChannel(Config::get('sockets.channel', 'lg-bridge:pipeline'));
        $this->app->singleton(EventRelay::class);
    }

    public function boot()
    {
        $log = $this->app->make('Giraffe\Logging\Log');
        /** @var callable $logger */
        $logger = \Config::get('logs.logger');
        $log->setLogger($logger());

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