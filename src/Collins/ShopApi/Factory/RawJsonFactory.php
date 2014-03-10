<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

use Collins\ShopApi;

class RawJsonFactory implements ResultFactoryInterface
{
    /**
     * @param ShopApi $shopApi
     */
    public function __construct($shopApi)
    {
    }

    public function createAutocomplete($json)
    {
        return $json;
    }

    public function createBasket($json)
    {
        return $json;
    }

    public function createCategoriesResult($json, $queryParams)
    {
        return $json;
    }

    public function createCategoryTree($json)
    {
        return $json;
    }

    public function createFacetList($json)
    {
        return $json;
    }

    public function createFacetsList($json)
    {
        return $json;
    }

    public function createProductsResult($json)
    {
        return $json;
    }

    public function createProductsEansResult($json)
    {
        return $json;
    }

    public function createProductSearchResult($json)
    {
        return $json;
    }

    public function createSuggest($json)
    {
        return $json;
    }

    public function createOrder($json)
    {
        return $json;
    }

   public function initiateOrder($json)
    {
        return $json;
    }

    public function createChildApps($json)
    {
        return $json;
    }

    public function preHandleError($json, $resultKey, $isMultiRequest)
    {
        return false;
    }
}