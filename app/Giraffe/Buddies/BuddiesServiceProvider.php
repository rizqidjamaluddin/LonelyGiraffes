<?php  namespace Giraffe\Buddies; 
use Event;
use Giraffe\Buddies\Requests\BuddyRequestService;
use Illuminate\Support\ServiceProvider;

class BuddiesServiceProvider extends ServiceProvider
{

    public function boot() {
        Event::subscribe(BuddyNotifier::class);
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