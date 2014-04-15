<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi\Constants;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractFacetManager implements FacetManagerInterface
{
    /**
     * IDs of the products, we already known, so we can skip them in #onFromJson().
     *
     * @var array
     */
    private $knownProductIds = array();

    /** @var Facet[][] */
    protected $facets = array();

    /** @var  \Collins\ShopApi */
    protected $shopApi;

    /**
     * facet groups and facets, which should be fetched lazily
     * by #prefetch(), if #getFacet() misses something.
     *
     * @var array
     */
    protected $missingFacetGroupIdsAndFacetIds = array();

    /**
     * @param \Collins\ShopApi $shopApi
     */
    public function setShopApi(ShopApi $shopApi)
    {
        $this->shopApi = $shopApi;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'collins.shop_api.product.create_model.before' => array('onProductFetched', 0),
            'collins.shop_api.autocompletion_result.create_model.before' => array('onBeforeCreateProductSearchResultModel', 0),
            'collins.shop_api.basket_result.create_model.before' => array('onBeforeCreateProductSearchResultModel', 0),
            'collins.shop_api.get_order_result.create_model.before' => array('onBeforeCreateOrderResultModel', 0),
            'collins.shop_api.product_search_result.create_model.before' => array('onBeforeCreateProductSearchResultModel', 0),
            'collins.shop_api.products_result.create_model.before' => array('onBeforeCreateProductsResultModel', 0),
            'collins.shop_api.products_eans_result.create_model.before' => array('onBeforeCreateProductsEansResultModel', 0),
        );
    }

    public function onProductFetched(GenericEvent $event)
    {
        $this->collectFacetIds($event->getSubject());
    }

    public function onBeforeCreateProductSearchResultModel(GenericEvent $event, $eventName, $dispatcher)
    {
        $jsonObject = $event->getSubject();
        if (!empty($jsonObject->products)) {
            foreach ($jsonObject->products as $productJsonObject) {
                $this->collectFacetIds($productJsonObject);
            }
        }


        if (!empty($jsonObject->facets)) {
            foreach ($jsonObject->facets as $groupId => $jsonTerms) {
                if (!ctype_digit($groupId)) continue;
                foreach ($jsonTerms->terms as $jsonTerm) {
                    $id = (int)$jsonTerm->term;
                    $this->missingFacetGroupIdsAndFacetIds[$groupId][] = $id;
                }
            }
        }
    }

    public function onBeforeCreateOrderResultModel(GenericEvent $event, $eventName, $dispatcher)
    {
        $jsonObject = $event->getSubject();

        foreach ($jsonObject->basket->products as $productJsonObject) {
            $this->collectFacetIds($productJsonObject);
        }
    }

    public function onBeforeCreateProductsResultModel(GenericEvent $event, $eventName, $dispatcher)
    {
        $jsonObject = $event->getSubject();

        foreach ($jsonObject->ids as $productJsonObject) {
            $this->collectFacetIds($productJsonObject);

            if (empty($productJsonObject->styles)) continue;

            foreach ($productJsonObject->styles as $styleJsonObject) {
                $this->collectFacetIds($styleJsonObject);
            }
        }
    }

    public function onBeforeCreateProductsEansResultModel(GenericEvent $event, $eventName, $dispatcher)
    {
        $jsonObject = $event->getSubject();

        foreach ($jsonObject->eans as $productJsonObject) {
            $this->collectFacetIds($productJsonObject);
        }
    }

    protected function collectFacetIds(\stdClass $productJsonObject)
    {
        if (!isset($productJsonObject->id) || isset($this->knownProductIds[$productJsonObject->id])) {
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

    abstract protected function preFetch();

    /**
     * {@inheritdoc}
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
     * @param integer[][] $facetGroupIds array with the structure array(<group id> => array(<facet id>,...),...)
     *
     * @return string[]
     */
    public function generateCacheKeys($facetGroupIds)
    {
        $cacheKeyNamespace = '\\Collins\\ShopApi\\' . (Constants::SDK_VERSION) . '\\Facet#';
        $keys = array();

        foreach ($facetGroupIds as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $keys[] = $cacheKeyNamespace . Facet::uniqueKey($groupId, $facetId);
            }
        }

        return $keys;
    }
} 
