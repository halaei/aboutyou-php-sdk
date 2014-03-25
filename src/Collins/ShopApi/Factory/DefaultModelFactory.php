<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi;

class DefaultModelFactory implements ModelFactoryInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /**
     * @param ShopApi $shopApi
     */
    public function __construct($shopApi)
    {
        ShopApi\Model\Autocomplete::setShopApi($shopApi);
        ShopApi\Model\BasketObject::setShopApi($shopApi);
        ShopApi\Model\Category::setShopApi($shopApi);
        ShopApi\Model\CategoriesResult::setShopApi($shopApi);
        ShopApi\Model\CategoryTree::setShopApi($shopApi);
        ShopApi\Model\Image::setShopApi($shopApi);
        ShopApi\Model\Product::setShopApi($shopApi);
        ShopApi\Model\FacetGroupSet::setShopApi($shopApi);
        ShopApi\Model\Variant::setShopApi($shopApi);

        $this->shopApi = $shopApi;
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
        return new ShopApi\Model\Autocomplete($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasket($json)
    {
        return new ShopApi\Model\Basket($json, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketItem(\stdClass $json, array $products)
    {
        return new ShopApi\Model\Basket\BasketItem($json, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketSet(\stdClass $json, array $products)
    {
        return new ShopApi\Model\Basket\BasketSet($json, $this, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createBasketSetItem(\stdClass $json, array $products)
    {
        return new ShopApi\Model\Basket\BasketVariantItem($json, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesResult($json, $queryParams)
    {
        return new ShopApi\Model\CategoriesResult($json, $queryParams['ids']);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategory(\stdClass $json, $parent = null)
    {
        return new ShopApi\Model\Category($json, $parent);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoryTree($json)
    {
        return new ShopApi\Model\CategoryTree($json);
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
        return new ShopApi\Model\Image($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct(\stdClass $json)
    {
        return new ShopApi\Model\Product($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsResult($json)
    {
        return new ShopApi\Model\ProductsResult($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsEansResult($json)
    {
        return new ShopApi\Model\ProductsEansResult($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createProductSearchResult($json)
    {
        return new ShopApi\Model\ProductSearchResult($json);
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
        return new ShopApi\Model\Variant($json);
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
        return new ShopApi\Model\InitiateOrder($json);
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
    public function createApp($json)
    {
        return new ShopApi\Model\App($json);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsCounts(\stdClass $jsonObject)
    {
        $termFacets = array();
        foreach ($jsonObject as $key => $jsonResultFacet) {
            $facets = $this->getTermFacets($jsonResultFacet->terms);

            $termFacets[$key] = new ShopApi\Model\ProductSearchResult\FacetCounts($key, $jsonResultFacet, $facets);
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
            $priceRanges[] = new ShopApi\Model\ProductSearchResult\PriceRange($range);
        }

        return $priceRanges;
    }

    /**
     * {@inheritdoc}
     */
    public function createSaleFacet(\stdClass $jsonObject)
    {
        return new ShopApi\Model\ProductSearchResult\SaleCounts($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesFacets(array $jsonArray)
    {
        $counts = array();
        foreach($jsonArray as $item) {
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
        if ($resultKey === 'basket') {
            return false;
        }

        if ($isMultiRequest) {
            return new ShopApi\Model\ResultError($json);
        }

        throw new ShopApi\Exception\ResultErrorException($json);
    }
}