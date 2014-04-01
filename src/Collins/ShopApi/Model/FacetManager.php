<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Constants;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Cache\CacheMultiGet;
use Symfony\Component\EventDispatcher\GenericEvent;

class FacetManager implements FacetManagerInterface, EventSubscriberInterface
{
    /** @var Facet[][] */
    private $facets = array();

    private $groups = array();

    /** @var CacheMultiGet */
    private $cache;

    /** @var  \Collins\ShopApi */
    private $shopApi;

    public function setShopApi($shopApi)
    {
        $this->shopApi = $shopApi;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'collins.shop_api.product_search_result.from_json.before' => array('onFromJson', 0),
#            'collins.shop_api.product.from_json.before' => array('onFromJson', 0),
        );
    }

    public function onFromJson(GenericEvent $event, $eventName, $dispatcher)
    {
        if(!empty($this->facets) && !empty($this->groups)) {
            return;
        }

        $jsonObject = $event->getArgument(0);

        $facetGroupIdsAndFacetIds = array();

        switch($eventName){
            case "collins.shop_api.product_search_result.from_json.before":
                foreach($jsonObject->products as $product) {
                    $facetGroupIdsAndFacetIds += Product::parseFacetIds($product);
                }
                break;
            case "collins.shop_api.product.from_json.before":
                $facetGroupIdsAndFacetIds = Product::parseFacetIds($jsonObject);
                break;
        }

        $missingFacetGroupIdsAndFacetIds = array();
        $missingFacetGroupIds = array_diff(array_keys($facetGroupIdsAndFacetIds), array_keys($this->groups));

        foreach($missingFacetGroupIds as $groupId) {
            $missingFacetGroupIdsAndFacetIds[$groupId] = $facetGroupIdsAndFacetIds[$groupId];
        }

        $this->preFetch($missingFacetGroupIdsAndFacetIds);
    }

//    public function parseJson(array $json)
//    {
//        foreach ($json as $singleFacet) {
//            $this->facets[$singleFacet->id][$singleFacet->facet_id] = $singleFacet;
//        }
//    }

    protected function preFetch($facetGroupIds)
    {
        if(empty($facetGroupIds)) {
            return;
        }

        /** @var  $cachedFacets Facet[] */
        #$cachedFacets = $this->cache->fetchMulti($this->generateCacheKeys($facetGroupIds));
        #$cachedFacets[0]->getGroupId();
        #throw new \Exception(print_r($facetGroupIds, true));

        $allFacets = $this->shopApi->fetchFacets(array_keys($facetGroupIds));

        foreach ($facetGroupIds as $groupId => $facetIds) {
            if(!isset($this->facets[$groupId])) {
                $this->facets[$groupId] = array();
            }

            foreach ($facetIds as $facetId) {
                $key = Facet::uniqueKey($groupId, $facetId);
                if (!isset($allFacets[$key])) {
                    // TODO: error handling
                    continue;
                }

                $facet = $allFacets[$key];
                $this->facets[$groupId][$facetId] = $facet;
                #$this->facets[$facet->getUniqueKey()] = $facet;
            }
        }
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
        if(!isset($this->facets[$groupId]) || !isset($this->facets[$groupId][$id])) {
            return(null);
        }

        $facet = $this->facets[$groupId][$id];
        if (!$facet instanceof Facet) {
            $this->facets[$groupId][$id] = $facet = Facet::createFromJson($facet);
        }

        return $facet;
    }

    /**
     * @param $type
     * @param $ids
     * @return array
     */
    public function generateCacheKeys($facetGroupIds)
    {
        $cacheKeyNamespace = '\\Collins\\ShopApi\\'.(Constants::SDK_VERSION)."\\Facet#";
        $keys = array();

        foreach ($facetGroupIds as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $keys[] = $cacheKeyNamespace.Facet::uniqueKey($groupId, $facetId);
            }
        }

        return($keys);
    }
} 