<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi;
use Collins\ShopApi\Model;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\CategoryManager\CategoryManagerInterface;
use Collins\ShopApi\Model\FacetManager\FacetManagerInterface;

class DefaultModelFactory implements ModelFactoryInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /** @var FacetManagerInterface */
    protected $facetManager;

    /** @var  CategoryManagerInterface */
    protected $categoryManager;

    /**
     * @param ShopApi $shopApi
     * @param FacetManagerInterface $facetManager
     * @param CategoryManagerInterface $categoryManager
     */
    public function __construct(
        ShopApi $shopApi = null,
        FacetManagerInterface $facetManager,
        CategoryManagerInterface $categoryManager
    ) {
        if (!empty($shopApi)) {
            $this->setShopApi($shopApi);
        }
        $this->categoryManager = $categoryManager;

        $this->setFacetManager($facetManager);
    }

    public function setShopApi(ShopApi $shopApi)
    {
        $this->shopApi = $shopApi;
    }

    /**
     * @param FacetManagerInterface $facetManager
     */
    public function setFacetManager(FacetManagerInterface $facetManager)
    {
        $this->facetManager = $facetManager;
        Model\FacetGroupSet::setFacetManager($facetManager);
    }

    /**
     * @return Model\FacetManager|FacetManagerInterface
     */
    public function getFacetManager()
    {
        return $this->facetManager;
    }

    public function setBaseImageUrl($baseUrl)
    {
        Model\Image::setBaseUrl($baseUrl);
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
     *
     * @return Model\Autocomplete
     */
    public function createAutocomplete(\stdClass $jsonObject)
    {
        return Model\Autocomplete::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket
     */
    public function createBasket(\stdClass $jsonObject)
    {
        return Basket::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketItem
     */
    public function createBasketItem(\stdClass $jsonObject, array $products)
    {
        return Basket\BasketItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSet
     */
    public function createBasketSet(\stdClass $jsonObject, array $products)
    {
        return Basket\BasketSet::createFromJson($jsonObject, $this, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSetItem
     */
    public function createBasketSetItem(\stdClass $jsonObject, array $products)
    {
        return Basket\BasketSetItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\CategoriesResult
     */
    public function createCategoriesResult(\stdClass $jsonObject, $queryParams)
    {
        return Model\CategoriesResult::createFromJson($jsonObject, $queryParams['ids'], $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Category
     */
    public function createCategory(\stdClass $jsonObject)
    {
        return Model\Category::createFromJson($jsonObject, $this->getCategoryManager());
    }

    /**
     * {@inheritdoc}
     *
     * @return CategoryTree
     */
    public function createCategoryTree($jsonArray)
    {
        $this->initializeCategoryManager($jsonArray);

        return new Model\CategoryTree($this->getCategoryManager());
    }

    public function setCategoryManager(CategoryManagerInterface $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * @return CategoryManagerInterface
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    public function initializeCategoryManager($jsonObject)
    {
        return $this->getCategoryManager()->parseJson($jsonObject, $this);
    }

    public function updateFacetManager($jsonObject)
    {
        $facets = $this->createFacetsList($jsonObject);
        $this->getFacetManager()->setFacets($facets);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet
     */
    public function createFacet(\stdClass $jsonObject)
    {
        return Model\Facet::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet[]
     */
    public function createFacetList(array $jsonArray)
    {
        $facets = array();
        foreach ($jsonArray as $jsonFacet) {
            $facet = $this->createFacet($jsonFacet);
            $key = $facet->getUniqueKey();
            $facets[$key] = $facet;
        }

        return $facets;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet[]
     */
    public function createFacetsList(\stdClass $jsonObject)
    {
        return $this->createFacetList($jsonObject->facet);
    }

    /**
     * {@inheritdoc}
     *
     * @return integer[]
     */
    public function createFacetTypes(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Image
     */
    public function createImage(\stdClass $jsonObject)
    {
        return Model\Image::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createProduct(\stdClass $jsonObject)
    {
        return Model\Product::createFromJson($jsonObject, $this, $this->shopApi->getAppId());
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\VariantsResult
     */
    public function createVariantsResult(\stdClass $jsonObject)
    {
        $variants = array();
        $errors = array();
        $productIds = array();
        $productSearchResult = false;

        foreach ($jsonObject as $id => $data) {
            if (isset($data->error_code)) {
                $errors[] = $id;
            } else {
                $variants[$data->id] = $data->product_id;

                $productIds[] = $data->product_id;
            }
        }

        if (count($productIds) > 0) {
            $productIds = array_unique($productIds);
            // search products for valid variants
            $productSearchResult = $this->shopApi
                ->fetchProductsByIds(
                    $productIds,
                    array(
                        ShopApi\Criteria\ProductFields::ATTRIBUTES_MERGED,
                        ShopApi\Criteria\ProductFields::BRAND,
                        ShopApi\Criteria\ProductFields::CATEGORIES,
                        ShopApi\Criteria\ProductFields::DEFAULT_IMAGE,
                        ShopApi\Criteria\ProductFields::DEFAULT_VARIANT,
                        ShopApi\Criteria\ProductFields::DESCRIPTION_LONG,
                        ShopApi\Criteria\ProductFields::DESCRIPTION_SHORT,
                        ShopApi\Criteria\ProductFields::IS_ACTIVE,
                        ShopApi\Criteria\ProductFields::IS_SALE,
                        ShopApi\Criteria\ProductFields::MAX_PRICE,
                        ShopApi\Criteria\ProductFields::MIN_PRICE,
                        ShopApi\Criteria\ProductFields::VARIANTS
                    )
                )
            ;
        }

        return ShopApi\Model\VariantsResult::create($variants, $errors, $productSearchResult);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createSingleProduct(\stdClass $jsonObject)
    {
        return $this->createProduct($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductsResult
     */
    public function createProductsResult(\stdClass $jsonObject)
    {
        return Model\ProductsResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductsEansResult
     */
    public function createProductsEansResult(\stdClass $jsonObject)
    {
        return Model\ProductsEansResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductSearchResult
     */
    public function createProductSearchResult(\stdClass $jsonObject)
    {
        return Model\ProductSearchResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function createSuggest(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Variant
     */
    public function createVariant(\stdClass $jsonObject, ShopApi\Model\Product $product)
    {
        return Model\Variant::createFromJson($jsonObject, $this, $product);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Order
     */
    public function createOrder(\stdClass $jsonObject)
    {
        $basket = $this->createBasket($jsonObject->basket);

        return new Model\Order($jsonObject->order_id, $basket);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\InitiateOrder
     */
    public function initiateOrder(\stdClass $jsonObject)
    {
        return Model\InitiateOrder::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\App[]
     */
    public function createChildApps(\stdClass $jsonObject)
    {
        $apps = array();
        foreach ($jsonObject->child_apps as $jsonApp) {
            $app = $this->createApp($jsonApp);
            $key = $app->getId();
            $apps[$key] = $app;
        }

        return $apps;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\App
     */
    public function createApp(\stdClass $jsonObject)
    {
        return Model\App::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsCounts(\stdClass $jsonObject)
    {
        $facetsCounts = array();

        foreach ($jsonObject as $groupId => $jsonResultFacet) {
            if (!ctype_digit($groupId)) {
                continue;
            }
            $facetCounts = $this->getTermFacets($groupId, $jsonResultFacet->terms);

            $facetsCounts[$groupId] = Model\ProductSearchResult\FacetCounts::createFromJson(
                $groupId,
                $jsonResultFacet,
                $facetCounts
            );
        }

        return $facetsCounts;
    }

    protected function getTermFacets($groupId, array $jsonTerms)
    {
        $facetManager = $this->facetManager;

        $facetCounts = array();
        foreach ($jsonTerms as $jsonTerm) {
            $id = (int)$jsonTerm->term;
            $facet = $facetManager->getFacet($groupId, $id);
            if ($facet === null) {
                continue;
            } // TODO: Handle error, write test
            $count = $jsonTerm->count;
            $facetCounts[] = new Model\ProductSearchResult\FacetCount($facet, $count);
        }

        return $facetCounts;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductSearchResult\PriceRange[]
     */
    public function createPriceRanges(\stdClass $jsonObject)
    {
        $priceRanges = array();
        foreach ($jsonObject->ranges as $range) {
            $priceRanges[] = Model\ProductSearchResult\PriceRange::createFromJson($range);
        }

        return $priceRanges;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductSearchResult\SaleCounts
     */
    public function createSaleFacet(\stdClass $jsonObject)
    {
        return Model\ProductSearchResult\SaleCounts::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesFacets(array $jsonArray)
    {
        $categoryManager = $this->getCategoryManager();

        $flattenCategories = array();
        foreach ($jsonArray as $item) {
            $id = $item->term;
            $category = $categoryManager->getCategory($id);
            if (!$category) continue;

            $category->setProductCount($item->count);
            $flattenCategories[$id] = $category;
        }

        return $flattenCategories;
    }

    public function preHandleError($json, $resultKey, $isMultiRequest)
    {
        if ($resultKey === 'basket' && isset($json->order_lines)) {
            return false;
        }

        if ($isMultiRequest) {
            return new Model\ResultError($json);
        }

        throw new ShopApi\Exception\ResultErrorException($json);
    }
}