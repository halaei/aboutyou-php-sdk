<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;
use Collins\ShopApi\Model\Facet;
use Doctrine\Common\Cache\CacheMultiGet as DoctrineCache;
use Aboutyou\Common\Cache\CacheMultiGet as AboutyouCache;

class AboutyouCacheStrategy implements FetchStrategyInterface
{
    /** @var FetchStrategyInterface */
    protected $chainedFetchStrategy;

    /** @var CacheMultiGet */
    public $cache;

    // cache each facet two hours
    const DEFAULT_CACHE_DURATION = 7200;
    protected $cacheDuration;

    /**
     * @param CacheMultiGet $cache
     * @param FetchStrategyInterface $facetStrategy
     * @param int $cacheDuration
     */
    public function __construct($cache, FetchStrategyInterface $facetStrategy, $cacheDuration = self::DEFAULT_CACHE_DURATION)
    {
        if (!($cache instanceof DoctrineCache || $cache instanceof AboutyouCache)) {
            throw new \InvalidArgumentException('$cache must be an instance of Aboutyou\\Common\\Cache\\CacheMultiGet');
        }

        $this->cache = $cache;
        $this->chainedFetchStrategy = $facetStrategy;
        $this->cacheDuration = max($cacheDuration, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($facetIds)
    {
        $keys = $this->generateCacheKeys($facetIds);
        $cachedFacets = $this->cache->fetchMulti($keys);
        $missedKeys = array_diff($keys, array_keys($cachedFacets));

        if (empty($missedKeys)) {
            return $cachedFacets;
        }

        $missedIds = $this->getIds($missedKeys);
        unset($missedKeys);

        $fetchedfactes = $this->chainedFetchStrategy->fetch($missedIds);
        $this->saveMulti($fetchedfactes);

        $facets = array_merge($cachedFacets, $fetchedfactes);

        return $facets;
    }

    /**
     * @param string[] $uniqueKeys
     * @return interger[][]
     */
    public function getIds($uniqueKeys)
    {
        $ids = array();
        foreach ($uniqueKeys as $uniqueKey) {
            list($groupId, $facteId) = explode(':', $uniqueKey);
            $ids[$groupId][] = $facteId;
        }

        return $ids;
    }

    /**
     * @param Facet[] $facets
     */
    public function saveMulti($facets)
    {
        foreach ($facets as $facet) {
            $this->cache->save($facet->getUniqueKey(), $facet, $this->cacheDuration);
        }
    }

    /**
     * This Method is useful to to store all Facets in a cache like memcache, redis or apc
     * @param ShopApi $shopApi
     */
    public function cacheAllFacets(ShopApi $shopApi)
    {
        $groupIds = $shopApi->fetchFacetTypes();

        $facets = $shopApi->fetchFacets($groupIds);

        $this->saveMulti($facets);
    }

    /**
     * @param integer[][] $facetIds array with the structure array(<group id> => array(<facet id>,...),...)
     *
     * @return string[]
     */
    public function generateCacheKeys($ids)
    {
//        $cacheKeyNamespace = '\\Collins\\ShopApi\\' . (Constants::SDK_VERSION) . '\\Facet#';
        $keys = array();

        foreach ($ids as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $keys[] = /*$cacheKeyNamespace .*/ Facet::uniqueKey($groupId, $facetId);
            }
        }

        return $keys;
    }
}