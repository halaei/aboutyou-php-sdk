<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi;

class DefaultModelFactory implements ModelFactoryInterface
{
    /**
     * @param ShopApi $shopApi
     */
    public function __construct($shopApi)
    {
        ShopApi\Model\Image::setShopApi($shopApi);
    }

    public function createAutocomplete($json)
    {
        return new ShopApi\Model\Autocomplete($json);
    }

    public function createBasket($json)
    {
        return new ShopApi\Model\Basket($json);
    }

    public function createCategoriesResult($json, $queryParams)
    {
        return new ShopApi\Model\CategoriesResult($json, $queryParams['ids']);
    }

    public function createCategoryTree($json)
    {
        return new ShopApi\Model\CategoryTree($json);
    }

    public function createFacet($json)
    {
        return ShopApi\Model\Facet::createFromJson($json);
    }

    public function createFacetList($json)
    {
        $facets = [];
        foreach ($json->facet as $jsonFacet) {
            $facet = $this->createFacet($jsonFacet);
            $key   = $facet->getUniqueKey();
            $facets[$key] = $facet;
        }

        return $facets;
    }

    public function createProduct($json)
    {
        return new ShopApi\Model\Product($json);
    }

    public function createProductsResult($json)
    {
        return new ShopApi\Model\ProductsResult($json);
    }

    public function createProductSearchResult($json)
    {
        return new ShopApi\Model\ProductSearchResult($json);
    }

    public function createSuggest($json)
    {
        return $json;
    }

    public function createVariant($json)
    {
        return new ShopApi\Model\Variant($json);
    }

    public function createOrder($json)
    {
        return new ShopApi\Model\Order($json);
    }
}