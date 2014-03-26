<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;


interface ResultFactoryInterface
{
    public function createAutocomplete($json);

    public function createBasket($json);

    public function createCategoriesResult($json, $queryParams);

    public function createCategoryTree($json);

    public function createFacetsList($json);

    public function createFacetList($json);

    public function createProductsResult($json);

    public function createProductsEansResult($json);

    public function createProductSearchResult($json);

    public function createSuggest($json);

    public function createOrder($json);

    public function initiateOrder($json);

    public function createChildApps($json);

    public function preHandleError($json, $resultKey, $isMultiRequest);

    public function setBaseImageUrl($baseUrl);
}