<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;


interface ResultFactoryInterface
{
    public function createAutocomplete($json);

    public function createBasket($json);

    public function createCategoriesResult($json, $queryParams);

    public function createCategoryTree($json);

    public function createFacetList($json);

    public function createProductsResult($json);

    public function createProductSearchResult($json);

    public function createSuggest($json);

    public function createOrder($json);

    public function initiateOrder($json);

    public function createChildApps($json);
}