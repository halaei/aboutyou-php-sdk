<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Constants;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Cache\CacheMultiGet;
use Symfony\Component\EventDispatcher\GenericEvent;

class FacetManager implements FacetManagerInterface, EventSubscriberInterface
{
    /**
     * IDs of the products, we already known, so we can skip them in #onFromJson().
     *
     * @var array
     */
    private $knownProductIds = array();

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
            'collins.shop_api.product.from_json.before' => array('onFromJson', 0),
            'collins.shop_api.products_result.from_json.before' => array('onFromJson', 0)
        );
    }

    public function onFromJson(GenericEvent $event, $eventName, $dispatcher)
    {
        $jsonObject = $event->getArgument(0);

        $facetGroupIdsAndFacetIds = array();

        switch ($eventName) {
            case "collins.shop_api.product_search_result.from_json.before":
                foreach ($jsonObject->products as $product) {
                    if (isset($this->knownProductIds[$product->id])) {
                        continue;
                    }
                    // @todo: optimize this.
                    //        Unfortunately we cannot combine the arrays
                    //        just by using array_merge() or the plus operator,
                    //        because we need to merge arrays of arrays (=>recursive merge)
                    //        without any renumbering!
                    foreach (Product::parseFacetIds($product) as $groupId => $facetIds) {
                        if (!isset($facetGroupIdsAndFacetIds[$groupId])) {
                            $facetGroupIdsAndFacetIds[$groupId] = $facetIds;
                        } else {
                            $facetGroupIdsAndFacetIds[$groupId] = array_merge($facetGroupIdsAndFacetIds[$groupId], $facetIds);
                        }
                    }

                    $this->knownProductIds[$product->id] = true;
                }
                break;
            case "collins.shop_api.product.from_json.before":
                if (isset($this->knownProductIds[$jsonObject->id])) {
                    return;
                }

                $facetGroupIdsAndFacetIds = Product::parseFacetIds($jsonObject);
                break;
        }

        $missingFacetGroupIdsAndFacetIds = array();
        $missingFacetGroupIds = array_diff(array_keys($facetGroupIdsAndFacetIds), array_keys($this->groups));

        foreach ($missingFacetGroupIds as $groupId) {
            $missingFacetGroupIdsAndFacetIds[$groupId] = $facetGroupIdsAndFacetIds[$groupId];
        }

        $this->preFetch($missingFacetGroupIdsAndFacetIds);
    }

    protected function preFetch($facetGroupIds)
    {
        if (empty($facetGroupIds)) {
            return;
        }

        /** @var  $cachedFacets Facet[] */
        $apiQueryParams = array();

        foreach ($facetGroupIds as $groupId => $facetIds) {
            $facetIds = array_values(array_unique($facetIds));

            foreach ($facetIds as $facetId) {
                $apiQueryParams[] = array('id' => $facetId, 'group_id' => $groupId);
            }
        }

        $this->facets += $this->shopApi->fetchFacet($apiQueryParams);

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
        $lookupKey = Facet::uniqueKey($groupId, $id);
        if (!isset($this->facets[$lookupKey])) {
            return (null);
        }

        return $this->facets[$lookupKey];
    }

    /**
     * @param $type
     * @param $ids
     * @return array
     */
    public function generateCacheKeys($facetGroupIds)
    {
        $cacheKeyNamespace = '\\Collins\\ShopApi\\' . (Constants::SDK_VERSION) . "\\Facet#";
        $keys = array();

        foreach ($facetGroupIds as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $keys[] = $cacheKeyNamespace . Facet::uniqueKey($groupId, $facetId);
            }
        }

        return ($keys);
    }
} 
