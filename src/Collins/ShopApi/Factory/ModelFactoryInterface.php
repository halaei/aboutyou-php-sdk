<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Factory;

interface ModelFactoryInterface extends ResultFactoryInterface
{
    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \Collins\ShopApi\Model\Basket\BasketItem
     */
    public function createBasketItem(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \Collins\ShopApi\Model\Basket\BasketSet
     */
    public function createBasketSet(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     * @param array $products
     *
     * @return \Collins\ShopApi\Model\Basket\BasketVariantItem
     */
    public function createBasketSetItem(\stdClass $json, array $products);

    /**
     * @param \stdClass $json
     * @param \Collins\ShopApi\Model\Category|null $parent
     *
     * @return \Collins\ShopApi\Model\Category
     */
    public function createCategory(\stdClass $json, $parent = null);

    /**
     * @param \stdClass $json
     *
     * @return \Collins\ShopApi\Model\Facet
     */
    public function createFacet(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \Collins\ShopApi\Model\Image
     */
    public function createImage(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \Collins\ShopApi\Model\Product
     */
    public function createProduct(\stdClass $json);

    /**
     * @param \stdClass $json
     *
     * @return \Collins\ShopApi\Model\Variant
     */
    public function createVariant(\stdClass $json);

    /***************************************+
     * ProductSearchResult Facets
     +++++++++++++++++++++++++++++++++++++++++*/

    /**
     * @param \stdClass $jsonObject
     *
     * @return \Collins\ShopApi\Model\ProductSearchResult\PriceRange[]
     */
    public function createPriceRanges(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return \Collins\ShopApi\Model\ProductSearchResult\FacetCounts[]
     */
    public function createFacetsCounts(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return \Collins\ShopApi\Model\ProductSearchResult\SaleCounts
     */
    public function createSaleFacet(\stdClass $jsonObject);

    /**
     * @param \stdClass[] $jsonObject
     *
     * @return \Collins\ShopApi\Model\ProductSearchResult\
     */
    public function createCategoriesFacets(array $jsonObject);
}
