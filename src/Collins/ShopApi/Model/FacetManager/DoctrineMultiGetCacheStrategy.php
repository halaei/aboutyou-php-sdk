<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Doctrine\Common\Cache\CacheMultiGet;

class DoctrineMultiGetCacheStrategy implements FetchStrategyInterface
{
    /** @var FetchStrategyInterface */
    protected $chainedFetchStrategy;

    /** @var CacheMultiGet */
    public $cache;

    /**
     * @param CacheMultiGet $cache
     * @param FetchStrategyInterface $facetStrategy
     */
    public function __construct(CacheMultiGet $cache, FetchStrategyInterface $facetStrategy)
    {
        $this->cache = $cache;
        $this->chainedFetchStrategy = $facetStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($facetIds)
    {
//        $keys = $this->generateCacheKeys($facetIds)
//        $cachedFacets = $this->cache->fetchMulti();
        $factes = $this->chainedFetchStrategy->fetch($facetIds);

        return $factes;
    }

    /**
     * @param integer[][] $facetIds array with the structure array(<group id> => array(<facet id>,...),...)
     *
     * @return string[]
     */
    public function generateCacheKeys($facetIds)
    {
        $cacheKeyNamespace = '\\Collins\\ShopApi\\' . (Constants::SDK_VERSION) . '\\Facet#';
        $keys = array();

        foreach ($facetIds as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $keys[] = $cacheKeyNamespace . Facet::uniqueKey($groupId, $facetId);
            }
        }

        return $keys;
    }
}