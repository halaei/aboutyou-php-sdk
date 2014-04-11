<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;


interface ResultFactoryInterface
{
    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createAutocomplete(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createBasket(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     * @param $queryParams
     *
     * @return mixed
     */
    public function createCategoriesResult(\stdClass $jsonObject, $queryParams);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createCategoryTree(\stdClass $jsonObject);

    /**
     * @param array $jsonArray
     *
     * @return mixed
     */
    public function createFacetList(array $jsonArray);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createFacetsList(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createProductsResult(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createProductsEansResult(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createProductSearchResult(\stdClass $jsonObject);

    /**
     * @param array $jsonArray
     *
     * @return mixed
     */
    public function createSuggest(array $jsonArray);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createOrder(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function initiateOrder(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return mixed
     */
    public function createChildApps(\stdClass $jsonObject);

    /**
     * @param array|\stdClass $json
     * @param string          $resultKey
     * @param boolean         $isMultiRequest
     *
     * @return mixed|false
     */
    public function preHandleError($json, $resultKey, $isMultiRequest);

    /**
     * @param $baseUrl
     */
    public function setBaseImageUrl($baseUrl);
}