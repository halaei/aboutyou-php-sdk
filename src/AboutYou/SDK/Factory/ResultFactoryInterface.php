<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Factory;


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
     * @param array $jsonArray
     *
     * @return mixed
     */
    public function createCategoryTree($jsonArray);

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
     * @param array $jsonArray
     *
     * @return mixed
     */
    public function createFacetTypes(array $jsonArray);

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