<?php  namespace Giraffe\Buddies; 
use Event;
use Giraffe\Buddies\Requests\BuddyRequestService;
use Giraffe\Common\EventRelay;
use Illuminate\Support\ServiceProvider;

class BuddiesServiceProvider extends ServiceProvider
{

    public function boot() {
        $this->app[EventRelay::class]->listen($this->app[BuddyNotifier::class]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BuddyService::class);
        $this->app->singleton(BuddyRequestService::class);
    }
}