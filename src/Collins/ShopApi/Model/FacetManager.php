<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Constants;
use Doctrine\Common\Cache\CacheMultiGet;
use Symfony\Component\EventDispatcher\GenericEvent;

class FacetManager implements FacetManagerInterface
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

    /**
     * facet groups and facets, which should be fetched lazily
     * by #prefech(), if #getFacet() misses something.
     *
     * @var array
     */
    private $missingFacetGroupIdsAndFacetIds = array();

    private $random;

    public function __construct() {
        $this->random = rand();
    }

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

        switch ($eventName) {
            case "collins.shop_api.product_search_result.from_json.before":
                foreach ($jsonObject->products as $productJsonObject) {
                    $this->onProductFetched($productJsonObject);
                }
                break;
            case "collins.shop_api.product.from_json.before":
                $this->onProductFetched($jsonObject);
                break;
        }
        echo("");
    }

    protected function onProductFetched($productJsonObject) {
        if (isset($this->knownProductIds[$productJsonObject->id])) {
            return;
        }

        // @todo: optimize this.
        //        Unfortunately we cannot combine the arrays
        //        just by using array_merge() or the plus operator,
        //        because we need to merge arrays of arrays (=>recursive merge)
        //        without any renumbering!
        foreach (Product::parseFacetIds($productJsonObject) as $groupId => $facetIds) {
            if (!isset($this->missingFacetGroupIdsAndFacetIds[$groupId])) {
                $this->missingFacetGroupIdsAndFacetIds[$groupId] = $facetIds;
            } else {
                $this->missingFacetGroupIdsAndFacetIds[$groupId] = array_merge($this->missingFacetGroupIdsAndFacetIds[$groupId], $facetIds);
            }
        }

        $this->knownProductIds[$productJsonObject->id] = true;
    }

    protected function preFetch()
    {
        if (empty($this->missingFacetGroupIdsAndFacetIds)) {
            return;
        }

        /** @var  $cachedFacets Facet[] */
        $apiQueryParams = array();

        foreach ($this->missingFacetGroupIdsAndFacetIds as $groupId => $facetIds) {
            unset($this->missingFacetGroupIdsAndFacetIds[$groupId]);

            $facetIds = array_values(array_unique($facetIds));

            foreach ($facetIds as $facetId) {
                $apiQueryParams[] = array('id' => $facetId, 'group_id' => $groupId);
            }
        }

        $this->facets += $this->shopApi->fetchFacet($apiQueryParams);

        #$this->missingFacetGroupIdsAndFacetIds = array();
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
            $this->preFetch();

            return (isset($this->facets[$lookupKey]) ? $this->facets[$lookupKey] : null);
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
