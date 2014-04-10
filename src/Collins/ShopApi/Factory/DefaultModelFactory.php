<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi;
use Collins\ShopApi\Model\FacetManager\FacetManagerInterface;

class DefaultModelFactory implements ModelFactoryInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /** @var FacetManagerInterface */
    protected $facetManager;

    /**
     * @param ShopApi $shopApi
     * @param FacetManagerInterface $facetManager
     */
    public function __construct(ShopApi $shopApi, FacetManagerInterface $facetManager)
    {
        ShopApi\Model\Category::setShopApi($shopApi);
        ShopApi\Model\Product::setShopApi($shopApi);
        ShopApi\Model\FacetGroupSet::setShopApi($shopApi);

        $this->shopApi = $shopApi;
        $this->setFacetManager($facetManager);
    }

    /**
     * @param FacetManagerInterface $facetManager
     */
    public function setFacetManager(FacetManagerInterface $facetManager)
    {
        if(!empty($this->facetManager)) {
            $oldFacetManagerSubscribedEvents = $this->facetManager->getSubscribedEvents();
            if(!empty($oldFacetManagerSubscribedEvents)) {
                $this->shopApi->getEventDispatcher()->removeSubscriber($this->facetManager);
            }
        }

        $newSubscribedEvents = $facetManager->getSubscribedEvents();
        if(!empty($newSubscribedEvents)) {
            $this->shopApi->getEventDispatcher()->addSubscriber($facetManager);
        }
        $this->facetManager = $facetManager;
        $this->facetManager->setShopApi($this->shopApi);
        ShopApi\Model\FacetGroupSet::setFacetManager($facetManager);
    }

    /**
     * @return ShopApi\Model\FacetManager|FacetManagerInterface
     */
    public function getFacetManager()
    {
        return $this->facetManager;
    }

    public function setBaseImageUrl($baseUrl)
    {
        ShopApi\Model\Image::setBaseUrl($baseUrl);
    }

    /**
     * @return ShopApi
     */
    protected function getShopApi()
    {
        return $this->shopApi;
    }

    /**
     * {@inheritdoc}
     */
    public function createAutocomplete($json)
    {
        return ShopApi\Model\Autocomplete::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasket($json)
    {
        return ShopApi\Model\Basket::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketItem(\stdClass $json, array $products)
    {
        return ShopApi\Model\Basket\BasketItem::createFromJson($json, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketSet(\stdClass $json, array $products)
    {
        return ShopApi\Model\Basket\BasketSet::createFromJson($json, $this, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketSetItem(\stdClass $json, array $products)
    {
        return ShopApi\Model\Basket\BasketSetItem::createFromJson($json, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesResult($json, $queryParams)
    {
        return ShopApi\Model\CategoriesResult::createFromJson($json, $queryParams['ids'], $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategory(\stdClass $json, $parent = null)
    {
        return ShopApi\Model\Category::createFromJson($json, $this, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoryTree($json)
    {
        return ShopApi\Model\CategoryTree::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacet(\stdClass $json)
    {
        return ShopApi\Model\Facet::createFromJson($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetList($json)
    {
        $facets = array();
        foreach ($json as $jsonFacet) {
            $facet = $this->createFacet($jsonFacet);
            $key   = $facet->getUniqueKey();
            $facets[$key] = $facet;
        }

        return $facets;
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsList($json)
    {
        return $this->createFacetList($json->facet);
    }

    /**
     * {@inheritdoc}
     */
    public function createImage(\stdClass $json)
    {
        return ShopApi\Model\Image::createFromJson($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct(\stdClass $json)
    {
        return ShopApi\Model\Product::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsResult($json)
    {
        return ShopApi\Model\ProductsResult::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsEansResult($json)
    {
        return ShopApi\Model\ProductsEansResult::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductSearchResult($json)
    {
        return ShopApi\Model\ProductSearchResult::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createSuggest($json)
    {
        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function createVariant(\stdClass $json)
    {
        return ShopApi\Model\Variant::createFromJson($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createOrder($json)
    {
        $basket = $this->createBasket($json->basket);

        return new ShopApi\Model\Order($json->order_id, $basket);
    }

    /**
     * {@inheritdoc}
     */
   public function initiateOrder($json)
    {
        return ShopApi\Model\InitiateOrder::createFromJson($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createChildApps($json)
    {
        $apps = array();
        foreach ($json->child_apps as $jsonApp) {
            $app = $this->createApp($jsonApp);
            $key   = $app->getId();
            $apps[$key] = $app;
        }

        return $apps;
    }

    /**
     * {@inheritdoc}
     */
    public function createApp(\stdClass $json)
    {
        return ShopApi\Model\App::createFromJson($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsCounts(\stdClass $jsonObject)
    {
        $termFacets = array();
        foreach ($jsonObject as $key => $jsonResultFacet) {
            $facets = $this->getTermFacets($jsonResultFacet->terms);

            $termFacets[$key] = ShopApi\Model\ProductSearchResult\FacetCounts::createFromJson($key, $jsonResultFacet, $facets);
        }

        return $termFacets;
    }

    protected function getTermFacets(array $facets)
    {
        return array();

        $api    = $this->getShopApi();
        $counts = array();

        foreach ($jsonTerms as $groudId => $jsonTerm) {
            $id = (int)$jsonTerm->term;
            $ids[] = array('id' => $id, 'group_id' => (int)$groudId);
            $counts[$groudId][$id] = $jsonTerm->count;
        }
        $facets = $api->fetchFacet($ids);

        return $facets;
    }

    /**
     * {@inheritdoc}
     */
    public function createPriceRanges(\stdClass $jsonObject)
    {
        $priceRanges = array();
        foreach ($jsonObject->ranges as $range) {
            $priceRanges[] = ShopApi\Model\ProductSearchResult\PriceRange::createFromJson($range);
        }

        return $priceRanges;
    }

    /**
     * {@inheritdoc}
     */
    public function createSaleFacet(\stdClass $jsonObject)
    {
        return ShopApi\Model\ProductSearchResult\SaleCounts::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesFacets(array $jsonArray)
    {
        $counts = array();
        foreach ($jsonArray as $item) {
            $categoryId = $item->term;
            $counts[$categoryId] = $item->count;
        }

        // fetch all categories from API
        $flattenCategories = $this->getShopApi()->fetchCategoriesByIds()->getCategories();

        foreach ($flattenCategories as $id => $category) {
            if (isset($counts[$category->getId()])) {
                $category->setProductCount($counts[$category->getId()]);
                if ($category->getParentId()) {
                    $parent = $flattenCategories[$category->getParentId()];
                    $parent->addChild($category);
                    $category->setParent($parent);
                }
            } else {
                unset($flattenCategories[$id]);
            }
        }

        return $flattenCategories;
    }

    public function preHandleError($json, $resultKey, $isMultiRequest)
    {
        if ($resultKey === 'basket' && isset($json->order_lines)) {
            return false;
        }

        if ($isMultiRequest) {
            return new ShopApi\Model\ResultError($json);
        }

        throw new ShopApi\Exception\ResultErrorException($json);
    }
}