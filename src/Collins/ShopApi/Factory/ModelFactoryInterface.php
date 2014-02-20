<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

interface ModelFactoryInterface
{
    public function createAutocomplete($json);

    public function createBasket($json);

    public function createCategoriesResult($json, $queryParams);

    public function createCategoryTree($json);

    public function createFacet($json);

    public function createFacetList($json);

    public function createProduct($json);

    public function createProductsResult($json);

    public function createProductSearchResult($json);

    public function createSuggest($json);

    public function createVariant($json);
}