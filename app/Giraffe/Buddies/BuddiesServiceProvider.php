<?php  namespace Giraffe\Buddies; 
use Giraffe\Buddies\BuddyRequests\BuddyRequestService;
use Illuminate\Support\ServiceProvider;

class BuddiesServiceProvider extends ServiceProvider
{

    public function boot() {

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