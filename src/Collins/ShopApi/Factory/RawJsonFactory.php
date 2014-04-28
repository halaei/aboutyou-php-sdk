<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
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

    /**
     * {@inheritdoc}
     */
    public function createAutocomplete(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createBasket(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoriesResult(\stdClass $jsonObject, $queryParams)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createCategoryTree(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetList(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetsList(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createFacetTypes(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsResult(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductsEansResult(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductSearchResult(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createSuggest(array $jsonArray)
    {
        return $jsonArray;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrder(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
   public function initiateOrder(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function createChildApps(\stdClass $jsonObject)
    {
        return $jsonObject;
    }

    /**
     * {@inheritdoc}
     */
    public function preHandleError($json, $resultKey, $isMultiRequest)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseImageUrl($baseUrl)
    {
        // not used
    }
}