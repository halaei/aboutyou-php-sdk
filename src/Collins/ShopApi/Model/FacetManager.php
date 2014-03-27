<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


use Doctrine\Common\Cache\CacheMultiGet;

class FacetManager implements FacetManagerInterface
{
    /** @var Facet[][] */
    private $facets;

    /** @var CacheMultiGet */
    private $cache;

    public function parseJson(array $json)
    {
        foreach ($json as $singleFacet) {
            $this->facets[$singleFacet->id][$singleFacet->facet_id] = $singleFacet;
        }
    }

    public function preFetch($brandIds, $facetGroupIds)
    {

    }

    /**
     * @param CacheMultiGet $cache
     */
    public function setCache(CacheMultiGet $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheMultiGet
     */
    public function getCache()
    {
        return $this->cache;
    }


    /**
     * @param $groupId
     * @param $id
     *
     * @return Facet
     */
    public function getFacet($groupId, $id)
    {
        $facet = $this->facets[$groupId][$id];
        if (!$facet instanceof Facet) {
            $this->facets[$groupId][$id] = $facet = Facet::createFromJson($facet);
        }

        return $facet;
    }
} 