<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Factory;

use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Exception\ResultErrorException;
use AboutYou\SDK\Model;
use AboutYou\SDK\Model\Basket;
use AboutYou\SDK\Model\WishList;
use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Model\CategoryManager\CategoryManagerInterface;
use AboutYou\SDK\Model\FacetManager\FacetManagerInterface;
use DateTime;
use stdClass;

class DefaultModelFactory implements ModelFactoryInterface
{
    /** @var \AY */
    protected $ay;

    /** @var FacetManagerInterface */
    protected $facetManager;

    /** @var  CategoryManagerInterface */
    protected $categoryManager;

    /**
     * @param \AY $ay
     * @param FacetManagerInterface $facetManager
     * @param CategoryManagerInterface $categoryManager
     */
    public function __construct(
        \AY $ay = null,
        FacetManagerInterface $facetManager,
        CategoryManagerInterface $categoryManager
    ) {
        if (!empty($ay)) {
            $this->setAY($ay);
        }
        $this->categoryManager = $categoryManager;

        $this->setFacetManager($facetManager);
    }

    public function setAY(\AY $ay)
    {
        $this->ay = $ay;
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
     * @return \AY
     */
    protected function getAY()
    {
        return $this->ay;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Autocomplete
     */
    public function createAutocomplete(stdClass $jsonObject)
    {
        return Model\Autocomplete::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function createSpellCorrection(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket
     */
    public function createBasket(stdClass $jsonObject)
    {
        return Basket::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketItem
     */
    public function createBasketItem(stdClass $jsonObject, array $products)
    {
        return Basket\BasketItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSet
     */
    public function createBasketSet(stdClass $jsonObject, array $products)
    {
        return Basket\BasketSet::createFromJson($jsonObject, $this, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSetItem
     */
    public function createBasketSetItem(stdClass $jsonObject, array $products)
    {
        return Basket\BasketSetItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return WishList
     */
    public function createWishList(stdClass $jsonObject)
    {
        return WishList::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return WishList\WishListItem
     */
    public function createWishListItem(stdClass $jsonObject, array $products)
    {
        return WishList\WishListItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSet
     */
    public function createWishListSet(stdClass $jsonObject, array $products)
    {
        return WishList\WishListSet::createFromJson($jsonObject, $this, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Basket\BasketSetItem
     */
    public function createWishListSetItem(stdClass $jsonObject, array $products)
    {
        return WishList\WishListSetItem::createFromJson($jsonObject, $products);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Category
     */
    public function createCategory(stdClass $jsonObject)
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
     * @return Model\Brand
     */
    public function createBrand(stdClass $jsonObject)
    {
        return Model\Brand::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet
     */
    public function createFacet(stdClass $jsonObject)
    {
        return Model\Facet::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet
     */
    public function createInlineFacet(stdClass $jsonObject)
    {
        return Model\Facet::createFromFacetsJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Facet[]
     */
    public function createFacetList(array $jsonArray)
    {
        $facets = [];
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
    public function createFacetsList(stdClass $jsonObject)
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
    public function createImage(stdClass $jsonObject)
    {
        return Model\Image::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createCompositionList(stdClass $jsonObject)
    {
        $compositions = [];

        foreach ($jsonObject as $name => $percentage) {
            $compositions[] = new Model\Composition($name, floatval($percentage)/100);
        }

        return $compositions;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createMaterialList(array $jsonArray)
    {
        $materials = [];

        foreach ($jsonArray as $jsonMaterial) {
            $materials[] = $this->createMaterial($jsonMaterial);
        }

        return $materials;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createMaterial(stdClass $jsonObject)
    {
        $compositions = $this->createCompositionList($jsonObject->composition);

        return new Model\Material(
            isset($jsonObject->name) ? $jsonObject->name : null,
            $compositions,
            isset($jsonObject->type) ? $jsonObject->type : null
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createProduct(stdClass $jsonObject)
    {
        return Model\Product::createFromJson($jsonObject, $this, $this->ay->getAppId());
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\VariantsResult
     */
    public function createVariantsResult(stdClass $jsonObject)
    {
        $variants = [];
        $errors = [];
        $productSearchResult = false;

        foreach ($jsonObject->variant_product as $variantId => $productId) {
            if (!is_numeric($productId)) {
                $errors[] = (int)$variantId;
            } else {
                $variants[$variantId] = (int)$productId;
            }
        }

        $productIds = array_unique($variants);
        if (count($productIds) > 0) {
            // search products for valid variants
            $productSearchResult = $this->ay
                ->fetchProductsByIds(
                    $productIds,
                    [
                        ProductFields::ATTRIBUTES_MERGED,
                        ProductFields::BRAND,
                        ProductFields::CATEGORIES,
                        ProductFields::DEFAULT_IMAGE,
                        ProductFields::DEFAULT_VARIANT,
                        ProductFields::DESCRIPTION_LONG,
                        ProductFields::DESCRIPTION_SHORT,
                        ProductFields::IS_ACTIVE,
                        ProductFields::IS_SALE,
                        ProductFields::MAX_PRICE,
                        ProductFields::MIN_PRICE,
                        ProductFields::VARIANTS,
                        ProductFields::INACTIVE_VARIANTS
                    ]
                )
            ;
        }

        return Model\VariantsResult::create($variants, $errors, $productSearchResult);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Product
     */
    public function createSingleProduct(stdClass $jsonObject)
    {
        return $this->createProduct($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductsResult
     */
    public function createProductsResult(stdClass $jsonObject)
    {
        return Model\ProductsResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\StylesResult
     */
    public function createStylesResult(stdClass $jsonObject)
    {
        return Model\StylesResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductsEansResult
     */
    public function createProductsEansResult(stdClass $jsonObject)
    {
        return Model\ProductsEansResult::createFromJson($jsonObject, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductSearchResult
     */
    public function createProductSearchResult(stdClass $jsonObject)
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
    public function createVariant(stdClass $jsonObject, Model\Product $product)
    {
        return Model\Variant::createFromJson($jsonObject, $this, $product);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\Order
     */
    public function createOrder(stdClass $jsonObject)
    {
        $basket = $this->createBasket($jsonObject->basket);

        return new Model\Order($jsonObject->order_id, $basket);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\InitiateOrder
     */
    public function initiateOrder(stdClass $jsonObject)
    {
        return Model\InitiateOrder::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\App[]
     */
    public function createChildApps(stdClass $jsonObject)
    {
        $apps = [];
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
    public function createApp(stdClass $jsonObject)
    {
        return Model\App::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsCounts(stdClass $jsonObject)
    {
        $facetsCounts = [];

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

    /**
     * {@inheritdoc}
     */
    public function createProductFacets(stdClass $jsonObject)
    {
        $facetsCounts = [];

        foreach ($jsonObject as $groupId => $jsonResultFacet) {
            if (!ctype_digit($groupId)) {
                continue;
            }
            $facetCounts = $this->getCountedFacets($jsonResultFacet->items);

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

        $facetCounts = [];
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

    protected function getCountedFacets(array $jsonCountedFacets)
    {
        $facetCounts = [];
        foreach ($jsonCountedFacets as $jsonCountedFacet) {
            $facet = $this->createInlineFacet($jsonCountedFacet);

            $count = isset($jsonCountedFacets->count) ? $jsonCountedFacets->count : 0;
            $facetCounts[] = new Model\ProductSearchResult\FacetCount($facet, $count);
        }

        return $facetCounts;
    }

    /**
     * {@inheritdoc}
     *
     * @return Model\ProductSearchResult\PriceRange[]
     */
    public function createPriceRanges(stdClass $jsonObject)
    {
        $priceRanges = [];
        foreach ($jsonObject->ranges as $range) {
            $priceRanges[] = Model\ProductSearchResult\PriceRange::createFromJson($range);
        }

        return $priceRanges;
    }

    /**
     * {@inheritdoc}
     */
    public function createSaleFacet(stdClass $jsonObject)
    {
        return Model\ProductSearchResult\SaleCounts::createFromJson($jsonObject);
    }

    /**
     * {@inheritdoc}
     */
    public function createNewInFacets(array $jsonObjects)
    {
        $newInFacets = [];
        foreach ($jsonObjects as $jsonObject) {
            $newInFacet = new ProductSearchResult\NewInCount(
                $jsonObject->count,
                $jsonObject->timestamp,
                new DateTime($jsonObject->date)
            );

            $newInFacets[] = $newInFacet;
        }


        return $newInFacets;
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesFacets(array $jsonArray)
    {
        $categoryManager = $this->getCategoryManager();

        $flattenCategories = [];
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

        throw new ResultErrorException($json);
    }
}
