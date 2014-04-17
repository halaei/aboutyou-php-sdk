<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\ProductSearchResult\FacetCounts;
use Doctrine\Common\Cache\CacheMultiGet;

class DoctrineMultiGetCacheStrategy implements FetchStrategyInterface
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
    public function __construct(CacheMultiGet $cache, FetchStrategyInterface $facetStrategy, $cacheDuration = self::DEFAULT_CACHE_DURATION)
    {
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
        $missedIds = $this->getIds(array_diff($keys, array_keys($cachedFacets)));

        $fetchedfactes = $this->chainedFetchStrategy->fetch($missedIds);

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
            $this->cache->save($facet->getUnique(), $facet, 1);
        }
    }

    /**
     * This Method is useful to to store all Facets in a cache like memcache, redis or apc
     * @param ShopApi $shopApi
     */
    public function cacheAllUsedFacets(ShopApi $shopApi)
    {
        $criteria = $shopApi->getProductSearchCriteria('DoctrineMultiGetCacheStrategy')
            ->setLimit(0)
            ->selectFacetsByGroupId(172, 3)
//            ->selectAllFacets(ShopApi\Criteria\ProductSearchCriteria::FACETS_UNLIMITED)
        ;

        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $facetCounts = $productSearchResult->getFacets();

        $this->saveMultiFacetCounts($facetCounts);
    }

    /**
     * @param FacetCounts[] $facetsCounts
     */
    public function saveMultiFacetCounts($facetsCounts)
    {
        $facets = array();
        foreach ($facetsCounts as $facetCounts) {
            foreach ($facetCounts->getFacetCounts() as $facetCount) {
                $facet = $facetCount->getFacet();
                $facets[$facet->getUniqueKey()] = $facet;
            }
        }

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