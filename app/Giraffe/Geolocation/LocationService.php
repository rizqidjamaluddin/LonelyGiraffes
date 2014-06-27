<?php  namespace Giraffe\Geolocation;

use Giraffe\Common\Service;
use Giraffe\Geolocation\NearbySearchableRepository;

class LocationService extends Service
{

    /**
     * @var LocationProvider[]
     */
    protected $providers;

    /**
     * @var LocationProvider
     */
    protected $canonicalProvider;

    public function pushProvider(LocationProvider $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Set the main location provider that can provide metadata (e.g. population counts) for providers
     * that don't hold their own databases.
     *
     * @param LocationProvider $provider
     * @return LocationProvider
     */
    public function setCanonicalProvider(LocationProvider $provider)
    {
        return $this->canonicalProvider = $provider;
    }

    public function getCanonicalProvider()
    {
        return $this->canonicalProvider;
    }

    public function search($hint, $limit = 5)
    {
        if (strlen($hint) < 2) {
            throw new LocationQueryTooShortException;
        }

        $results = [];

        // loop through providers and grab all results
        foreach ($this->providers as $provider) {
            $results = array_merge($results, $provider->search($hint));
        }

        return $results;
    }

    /**
     * Get entities from a repository regarded to as "nearby" according to a particular strategy.
     * Will use the default strategy if not set. Repository must implement the strategy's respective
     * repository interface.
     *
     * @param Locatable                  $location
     * @param NearbySearchableRepository $repository
     * @param array                      $options
     * @param NearbySearchStrategy|null  $strategy
     */
    public function getNearbyFromRepository(Locatable $location, NearbySearchableRepository $repository, $options = [], NearbySearchStrategy $strategy = null)
    {

    }
} 