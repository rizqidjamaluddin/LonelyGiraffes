<?php  namespace Giraffe\Geolocation;

use Giraffe\Common\ConfigurationException;
use Giraffe\Common\Service;
use Giraffe\Common\ValidationException;
use Giraffe\Geolocation\NearbySearchableRepository;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    /**
     * @var NearbySearchStrategy
     */
    protected $defaultNearbySearchStrategy;

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

    public function setDefaultNearbySearchStrategy(NearbySearchStrategy $nearbySearchStrategy)
    {
        $this->defaultNearbySearchStrategy = $nearbySearchStrategy;
    }

    /**
     * @throws \Giraffe\Common\ConfigurationException
     * @return NearbySearchStrategy
     */
    public function getDefaultNearbySearchStrategy()
    {
        if ($this->defaultNearbySearchStrategy) {
            return $this->defaultNearbySearchStrategy;
        } else {
            throw new ConfigurationException('No default search strategy set');
        }
    }

    public function search($hint, $limit = 5)
    {
        if (strlen($hint) < 2) {
            throw new LocationQueryTooShortException;
        }

        $results = new Collection;

        // loop through providers and grab all results
        foreach ($this->providers as $provider) {
            $results = $results->merge($provider->search($hint));
        }

        $filter = function ($location) {
            return $location->population ?: 1;
        };
        $results = $results->sortBy($filter, SORT_NUMERIC, true);


        return $results;
    }

    /**
     * Get entities from a repository regarded to as "nearby" according to a particular strategy.
     * Will use the default strategy if not set. Repository must implement the strategy's respective
     * repository interface.
     *
     * @param Locatable                  $locatable
     * @param NearbySearchableRepository $repository
     * @param array                      $options
     * @param NearbySearchStrategy|null  $strategy
     * @return array
     * @throws \Giraffe\Common\ConfigurationException
     */
    public function getNearbyFromRepository(
        Locatable $locatable,
        NearbySearchableRepository $repository,
        $options = [],
        NearbySearchStrategy $strategy = null
    ) {
        if (!$strategy) {
            $strategy = $this->getDefaultNearbySearchStrategy();
        }
        return $strategy->searchRepository($locatable->getLocation(), $repository, $options);
    }

    public function getCacheStringFromAttributesArray($attributes)
    {
        if (array_key_exists('city', $attributes) ||
            array_key_exists('state', $attributes) ||
            array_key_exists('country', $attributes)
        ) {
            // if one exists, they all have to
            if (!(array_key_exists('city', $attributes) &&
                array_key_exists('state', $attributes) &&
                array_key_exists('country', $attributes))
            ) {
                throw new ValidationException('Given location invalid; city, state and country required', []);
            }

            // build location object, then load it back into the attributes; this will 404 if location is missing
            try {
                $location = Location::buildFromCity($attributes['city'], $attributes['state'], $attributes['country']);
            } catch (NotFoundLocationException $e) {
                throw new ValidationException('Given location invalid', []);
            }

            // set cache data if possible
            $cacheString = $this->getDefaultNearbySearchStrategy()->getCacheMetadata($location);
            return $cacheString;
        }
        return false;
    }

} 