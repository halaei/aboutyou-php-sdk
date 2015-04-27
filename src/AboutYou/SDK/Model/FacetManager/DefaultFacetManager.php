<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\FacetManager;

use Aboutyou\Common\Cache\Cache;
use AboutYou\SDK\Model\Facet;

class DefaultFacetManager implements FacetManagerInterface
{
    const DEFAULT_CACHE_DURATION = 7200;

    /** @var Facet[][] */
    private $facets = null;

    /** @var Cache */
    private $cache;

    /** @var string */
    private $cacheKey;

    /**
     * @param Cache $cache
     * @param string $appId
     */
    public function __construct(Cache $cache = null, $appId = '')
    {
        $this->cache    = $cache;
        $this->cacheKey = 'AY:SDK:' . $appId . ':facets';

        $this->loadCachedFacets();
    }

    public function loadCachedFacets()
    {
        if ($this->cache) {
            $this->facets = $this->cache->fetch($this->cacheKey) ?: null;
        }
    }

    public function cacheFacets()
    {
        if ($this->cache) {
            $this->cache->save($this->cacheKey, $this->facets, self::DEFAULT_CACHE_DURATION);
        }
    }

    public function clearCache()
    {
        if ($this->cache) {
            $this->cache->delete($this->cacheKey);
        }
    }

    public function isEmpty()
    {
        return $this->facets === null;
    }

    public function setFacets($facets)
    {
        $this->facets = $facets;
        $this->cacheFacets();
    }

    /**
     * {@inheritdoc}
     */
    public function getFacet($groupId, $id)
    {
        $lookupKey = Facet::uniqueKey($groupId, $id);

        return isset($this->facets[$lookupKey]) ? $this->facets[$lookupKey] : null;
    }

    /**
     * @param int[] $groups
     * @return array
     */
    public function getFacetsByGroups($groups)
    {
        return [];
    }
}
